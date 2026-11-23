<?php

require_once __DIR__ . '/recepcionista_auth.php';
requerir_recepcionista('../view/recepcionista/lista_recepcionista.php');
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/odontologo_catalogos.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../view/cita/lista_citas.php');
    exit();
}

if (!validar_csrf_recepcionista()) {
    $_SESSION['mensaje'] = 'La sesión del formulario venció. Recarga la página e inténtalo de nuevo.';
    $_SESSION['tipo_mensaje'] = 'error';
    header('Location: ../view/cita/lista_citas.php');
    exit();
}

function traducirErrorCita(PDOException $e, string $fallback): string
{
    $raw = $e->getMessage();
    $code = $e->getCode();

    if ($code === '23505') {
        return 'El odontólogo ya tiene una cita en esa misma fecha y hora.';
    }
    if (str_contains($raw, 'El paciente ya cuenta con una cita')) {
        return 'El paciente ya tiene una cita registrada para ese mismo día (máximo 1 cita por día).';
    }
    if (str_contains($raw, 'Deben pasar al menos 4 días')) {
        return 'Deben pasar al menos 4 días completos entre citas para el mismo paciente.';
    }
    if (str_contains($raw, 'El odontólogo ya tiene una cita asignada')) {
        return 'El odontólogo seleccionado ya tiene una cita asignada ese día (máximo 1 cita por odontólogo al día).';
    }
    if (str_contains($raw, 'no coincide con el servicio solicitado') || str_contains($raw, 'RN-21')) {
        return 'Solo puedes asignar un odontólogo cuya especialidad coincida con el servicio solicitado por el paciente (RN-21).';
    }
    if (str_contains($raw, 'ck_cita_horario')) {
        return 'El horario debe estar entre 08:00 y 19:30 en intervalos de 30 minutos.';
    }
    if (str_contains($raw, 'ck_cita_cancelacion')) {
        return 'Debes ingresar un motivo de cancelación para cancelar la cita.';
    }

    return $fallback;
}

$accion = $_POST['accion'] ?? (isset($_POST['actualizar_cita']) ? 'confirmar' : 'registrar');
$recepcionistaId = (int) $_SESSION['recepcionista_id'];

if ($accion === 'confirmar') {
    $citaId = filter_var($_POST['cita_id'] ?? null, FILTER_VALIDATE_INT);
    $odontologoId = filter_var($_POST['odontologo_id'] ?? null, FILTER_VALIDATE_INT);

    if ($citaId === false || $citaId <= 0 || $odontologoId === false || $odontologoId <= 0) {
        $_SESSION['mensaje'] = 'Selecciona un odontólogo válido para confirmar la cita.';
        $_SESSION['tipo_mensaje'] = 'error';
    } else {
        try {
            $stmt = $conexion->prepare('SELECT id, nombre, especialidad FROM odontologo WHERE id = ? AND activo IS TRUE');
            $stmt->execute([$odontologoId]);
            $odontologoRow = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$odontologoRow) {
                $_SESSION['mensaje'] = 'El odontólogo seleccionado no está disponible.';
                $_SESSION['tipo_mensaje'] = 'error';
            } else {
                $stmtCitaInfo = $conexion->prepare(
                    'SELECT c.fecha, s.nombre AS servicio_nombre
                     FROM cita c
                     INNER JOIN servicio s ON s.id = c.servicio_id
                     WHERE c.id = ?
                     LIMIT 1'
                );
                $stmtCitaInfo->execute([$citaId]);
                $citaRow = $stmtCitaInfo->fetch(PDO::FETCH_ASSOC);
                $fechaCita = $citaRow['fecha'] ?? false;
                $servicioCita = trim((string) ($citaRow['servicio_nombre'] ?? ''));

                if (!$citaRow) {
                    $_SESSION['mensaje'] = 'No se encontró la cita solicitada.';
                    $_SESSION['tipo_mensaje'] = 'error';
                } elseif (!coincideEspecialidadConServicio((string) ($odontologoRow['especialidad'] ?? ''), $servicioCita)) {
                    $_SESSION['mensaje'] = "Solo puedes asignar un odontólogo especializado en \"{$servicioCita}\". El especialista seleccionado ({$odontologoRow['nombre']}) tiene la especialidad \"{$odontologoRow['especialidad']}\" (RN-21).";
                    $_SESSION['tipo_mensaje'] = 'error';
                } else {
                    $stmtConflicto = $conexion->prepare(
                        "SELECT id FROM cita
                         WHERE odontologo_id = ?
                           AND fecha = ?
                           AND id <> ?
                           AND estado IN ('confirmada', 'completada')
                         LIMIT 1"
                    );
                    $stmtConflicto->execute([$odontologoId, $fechaCita, $citaId]);

                    if ($stmtConflicto->fetchColumn()) {
                        $_SESSION['mensaje'] = "El odontólogo seleccionado ya tiene una cita asignada para el día {$fechaCita}. Cada odontólogo solo puede atender máximo 1 cita por día.";
                        $_SESSION['tipo_mensaje'] = 'error';
                    } else {
                        $stmt = $conexion->prepare(
                            "UPDATE cita
                             SET odontologo_id = ?, estado = 'confirmada', recepcionista_id = ?
                             WHERE id = ? AND estado = 'pendiente'
                             RETURNING id"
                        );
                        $stmt->execute([$odontologoId, $recepcionistaId, $citaId]);

                        if ($stmt->fetchColumn()) {
                            $_SESSION['mensaje'] = 'La cita fue confirmada y el odontólogo especializado quedó asignado.';
                            $_SESSION['tipo_mensaje'] = 'exito';
                        } else {
                            $_SESSION['mensaje'] = 'La cita ya no está pendiente. Actualiza la página para ver su estado.';
                            $_SESSION['tipo_mensaje'] = 'error';
                        }
                    }
                }
            }
        } catch (PDOException $e) {
            error_log('Error al confirmar y asignar una cita: ' . $e->getMessage());
            $_SESSION['mensaje'] = traducirErrorCita($e, 'No se pudo confirmar la cita. Verifica que el odontólogo no tenga otra cita en ese horario.');
            $_SESSION['tipo_mensaje'] = 'error';
        }
    }

    header('Location: ../view/cita/lista_citas.php');
    exit();
}

if ($accion === 'cancelar') {
    $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
    $motivoCancelacion = trim($_POST['motivo_cancelacion'] ?? '');

    if ($id === false || $id === null || $id <= 0) {
        $_SESSION['mensaje'] = 'Error: el ID de la cita no es válido.';
        $_SESSION['tipo_mensaje'] = 'error';
    } elseif ($motivoCancelacion === '') {
        $_SESSION['mensaje'] = 'Debes indicar el motivo de cancelación.';
        $_SESSION['tipo_mensaje'] = 'error';
    } else {
        try {
            $stmt = $conexion->prepare(
                "UPDATE cita
                 SET estado = 'cancelada',
                     motivo_cancelacion = :motivo_cancelacion,
                     recepcionista_id = :recepcionista_id
                 WHERE id = :id
                   AND estado IN ('pendiente', 'confirmada')
                 RETURNING id"
            );
            $stmt->execute([
                ':motivo_cancelacion' => substr($motivoCancelacion, 0, 200),
                ':recepcionista_id' => $recepcionistaId,
                ':id' => $id,
            ]);

            if ($stmt->fetchColumn()) {
                $_SESSION['mensaje'] = 'La cita fue cancelada correctamente.';
                $_SESSION['tipo_mensaje'] = 'exito';
            } else {
                $_SESSION['mensaje'] = 'Solo se pueden cancelar citas en estado pendiente o confirmada.';
                $_SESSION['tipo_mensaje'] = 'error';
            }
        } catch (PDOException $e) {
            error_log('Error al cancelar cita: ' . $e->getMessage());
            $_SESSION['mensaje'] = traducirErrorCita($e, 'No se pudo cancelar la cita.');
            $_SESSION['tipo_mensaje'] = 'error';
        }
    }

    header('Location: ../view/cita/lista_citas.php');
    exit();
}

if ($accion === 'marcar_no_show') {
    $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);

    if ($id === false || $id === null || $id <= 0) {
        $_SESSION['mensaje'] = 'Error: el ID de la cita no es válido.';
        $_SESSION['tipo_mensaje'] = 'error';
    } else {
        try {
            $stmt = $conexion->prepare(
                "UPDATE cita
                 SET estado = 'no_show',
                     recepcionista_id = :recepcionista_id
                 WHERE id = :id
                   AND estado = 'confirmada'
                 RETURNING id"
            );
            $stmt->execute([
                ':recepcionista_id' => $recepcionistaId,
                ':id' => $id,
            ]);

            if ($stmt->fetchColumn()) {
                $_SESSION['mensaje'] = 'La cita fue marcada como inasistencia (No asistió).';
                $_SESSION['tipo_mensaje'] = 'exito';
            } else {
                $_SESSION['mensaje'] = 'Solo se puede registrar inasistencia sobre citas confirmadas.';
                $_SESSION['tipo_mensaje'] = 'error';
            }
        } catch (PDOException $e) {
            error_log('Error al marcar inasistencia (no_show): ' . $e->getMessage());
            $_SESSION['mensaje'] = traducirErrorCita($e, 'No se pudo marcar la cita como inasistencia.');
            $_SESSION['tipo_mensaje'] = 'error';
        }
    }

    header('Location: ../view/cita/lista_citas.php');
    exit();
}

// Política de control de agenda (RF-009 / RF-038): Recepción NO crea ni edita citas manualmente;
// el agendamiento es exclusivo del paciente titular y recepción solo direcciona/asigna odontólogo, confirma, marca no_show o cancela con motivo.
$_SESSION['mensaje'] = 'Por política de agendamiento, solo el paciente puede solicitar sus citas. Recepción gestiona exclusivamente el direccionamiento a odontólogos, confirmación, registro de inasistencia o cancelación con motivo.';
$_SESSION['tipo_mensaje'] = 'error';
header('Location: ../view/cita/lista_citas.php');
exit();
