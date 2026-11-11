<?php

require_once __DIR__ . '/recepcionista_auth.php';
requerir_recepcionista('../view/recepcionista/lista_recepcionista.php');
require_once __DIR__ . '/../config/database.php';

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
    if (str_contains($raw, 'ck_cita_horario')) {
        return 'El horario debe estar entre 08:00 y 19:30 en intervalos de 30 minutos.';
    }
    if (str_contains($raw, 'ck_cita_cancelacion')) {
        return 'Debes ingresar un motivo de cancelación para cancelar la cita.';
    }

    return $fallback;
}

$accion = $_POST['accion'] ?? 'registrar';
$recepcionistaId = (int) $_SESSION['recepcionista_id'];

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

if ($accion === 'actualizar') {
    $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
    $pacienteId = filter_var($_POST['paciente_id'] ?? null, FILTER_VALIDATE_INT);
    $odontologoRaw = trim((string) ($_POST['odontologo_id'] ?? ''));
    $odontologoId = $odontologoRaw === '' ? null : filter_var($odontologoRaw, FILTER_VALIDATE_INT);
    $servicioId = filter_var($_POST['servicio_id'] ?? null, FILTER_VALIDATE_INT);
    $fecha = trim($_POST['fecha'] ?? '');
    $hora = substr(trim($_POST['hora'] ?? ''), 0, 5);
    $estado = trim($_POST['estado'] ?? '');
    $motivo = trim($_POST['motivo'] ?? '');
    $motivoCancelacion = trim($_POST['motivo_cancelacion'] ?? '');

    $estadosValidos = ['pendiente', 'confirmada', 'completada', 'cancelada', 'no_show'];
    $fechaObj = DateTimeImmutable::createFromFormat('!Y-m-d', $fecha);
    $fechaValida = $fechaObj !== false && $fechaObj->format('Y-m-d') === $fecha;
    $horaValida = preg_match('/^(?:0[89]|1[0-9]):(?:00|30)$/', $hora) === 1;

    if ($id === false || $id === null || $id <= 0) {
        $_SESSION['mensaje'] = 'Error: el ID de la cita no es válido.';
        $_SESSION['tipo_mensaje'] = 'error';
        header('Location: ../view/cita/lista_citas.php');
        exit();
    }

    $destinoError = '../view/cita/editar_cita.php?id=' . $id;

    if ($pacienteId === false || $pacienteId <= 0 || $servicioId === false || $servicioId <= 0) {
        $_SESSION['mensaje'] = 'Selecciona un paciente y un servicio válidos.';
        $_SESSION['tipo_mensaje'] = 'error';
        header('Location: ' . $destinoError);
        exit();
    }

    if ($odontologoId === false || ($odontologoId !== null && $odontologoId <= 0)) {
        $_SESSION['mensaje'] = 'El odontólogo seleccionado no es válido.';
        $_SESSION['tipo_mensaje'] = 'error';
        header('Location: ' . $destinoError);
        exit();
    }

    if (!in_array($estado, $estadosValidos, true)) {
        $_SESSION['mensaje'] = 'El estado de la cita no es válido.';
        $_SESSION['tipo_mensaje'] = 'error';
        header('Location: ' . $destinoError);
        exit();
    }

    if (in_array($estado, ['confirmada', 'completada'], true) && $odontologoId === null) {
        $_SESSION['mensaje'] = 'Para confirmar o completar una cita debes asignar un odontólogo.';
        $_SESSION['tipo_mensaje'] = 'error';
        header('Location: ' . $destinoError);
        exit();
    }

    if (!$fechaValida || !$horaValida) {
        $_SESSION['mensaje'] = 'Elige una fecha válida y un horario entre 08:00 y 19:30 en intervalos de 30 minutos.';
        $_SESSION['tipo_mensaje'] = 'error';
        header('Location: ' . $destinoError);
        exit();
    }

    if ($estado === 'cancelada' && $motivoCancelacion === '') {
        $_SESSION['mensaje'] = 'Para cambiar el estado a cancelada debes escribir el motivo de cancelación.';
        $_SESSION['tipo_mensaje'] = 'error';
        header('Location: ' . $destinoError);
        exit();
    }

    try {
        $stmt = $conexion->prepare(
            'UPDATE cita
             SET paciente_id = :paciente_id,
                 odontologo_id = :odontologo_id,
                 servicio_id = :servicio_id,
                 recepcionista_id = :recepcionista_id,
                 fecha = :fecha,
                 hora = :hora,
                 estado = :estado,
                 motivo = :motivo,
                 motivo_cancelacion = :motivo_cancelacion
             WHERE id = :id'
        );
        $stmt->execute([
            ':paciente_id' => $pacienteId,
            ':odontologo_id' => $odontologoId,
            ':servicio_id' => $servicioId,
            ':recepcionista_id' => $recepcionistaId,
            ':fecha' => $fecha,
            ':hora' => $hora,
            ':estado' => $estado,
            ':motivo' => $motivo === '' ? null : substr($motivo, 0, 200),
            ':motivo_cancelacion' => $estado === 'cancelada' ? substr($motivoCancelacion, 0, 200) : null,
            ':id' => $id,
        ]);

        $_SESSION['mensaje'] = '¡Cita actualizada correctamente!';
        $_SESSION['tipo_mensaje'] = 'exito';
        header('Location: ../view/cita/lista_citas.php');
        exit();
    } catch (PDOException $e) {
        error_log('Error al actualizar cita: ' . $e->getMessage());
        $_SESSION['mensaje'] = traducirErrorCita($e, 'No se pudo actualizar la cita. Verifica los datos y reglas de agenda.');
        $_SESSION['tipo_mensaje'] = 'error';
        header('Location: ' . $destinoError);
        exit();
    }
}

// REGISTRAR NUEVA CITA MANUALMENTE DESDE RECEPCIÓN
$pacienteId = filter_var($_POST['paciente_id'] ?? null, FILTER_VALIDATE_INT);
$odontologoRaw = trim((string) ($_POST['odontologo_id'] ?? ''));
$odontologoId = $odontologoRaw === '' ? null : filter_var($odontologoRaw, FILTER_VALIDATE_INT);
$servicioId = filter_var($_POST['servicio_id'] ?? null, FILTER_VALIDATE_INT);
$fecha = trim($_POST['fecha'] ?? '');
$hora = substr(trim($_POST['hora'] ?? ''), 0, 5);
$motivo = trim($_POST['motivo'] ?? '');

$fechaObj = DateTimeImmutable::createFromFormat('!Y-m-d', $fecha);
$fechaValida = $fechaObj !== false && $fechaObj->format('Y-m-d') === $fecha;
$horaValida = preg_match('/^(?:0[89]|1[0-9]):(?:00|30)$/', $hora) === 1;

if ($pacienteId === false || $pacienteId <= 0 || $servicioId === false || $servicioId <= 0 || $odontologoId === false) {
    $_SESSION['mensaje'] = 'Selecciona paciente, servicio y odontólogo válidos.';
    $_SESSION['tipo_mensaje'] = 'error';
    header('Location: ../view/cita/form_cita.php');
    exit();
}

if (!$fechaValida || !$horaValida) {
    $_SESSION['mensaje'] = 'Elige una fecha válida y un horario entre 08:00 y 19:30 en intervalos de 30 minutos.';
    $_SESSION['tipo_mensaje'] = 'error';
    header('Location: ../view/cita/form_cita.php');
    exit();
}

try {
    $estadoInicial = $odontologoId !== null ? 'confirmada' : 'pendiente';

    $sql = 'INSERT INTO cita (paciente_id, odontologo_id, servicio_id, recepcionista_id, fecha, hora, motivo, estado)
            VALUES (:paciente_id, :odontologo_id, :servicio_id, :recepcionista_id, :fecha, :hora, :motivo, :estado)';

    $stmt = $conexion->prepare($sql);
    $stmt->execute([
        ':paciente_id' => $pacienteId,
        ':odontologo_id' => $odontologoId,
        ':servicio_id' => $servicioId,
        ':recepcionista_id' => $recepcionistaId,
        ':fecha' => $fecha,
        ':hora' => $hora,
        ':motivo' => $motivo === '' ? null : substr($motivo, 0, 200),
        ':estado' => $estadoInicial,
    ]);

    $_SESSION['mensaje'] = $estadoInicial === 'confirmada'
        ? '¡Cita agendada y confirmada exitosamente!'
        : '¡Cita registrada como pendiente exitosamente!';
    $_SESSION['tipo_mensaje'] = 'exito';
    header('Location: ../view/cita/lista_citas.php');
    exit();
} catch (PDOException $e) {
    error_log('Error al agendar cita desde recepción: ' . $e->getMessage());
    $_SESSION['mensaje'] = traducirErrorCita($e, 'Error al agendar la cita. Verifica las reglas de disponibilidad.');
    $_SESSION['tipo_mensaje'] = 'error';
    header('Location: ../view/cita/form_cita.php');
    exit();
}
