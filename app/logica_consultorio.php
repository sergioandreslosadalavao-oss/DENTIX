<?php
// app/logica_consultorio.php - Gestión dinámica de consultorios por sede (Crear y Eliminar consultorios libres - RF-041 / RN-20)
require_once __DIR__ . '/recepcionista_auth.php';
requerir_recepcionista('../view/recepcionista/lista_recepcionista.php');
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/odontologo_catalogos.php';

$destino = '../view/odontologo/lista_odontologos.php#panel-consultorios';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . $destino);
    exit;
}

if (!validar_csrf_recepcionista()) {
    $_SESSION['mensaje'] = 'La sesión del formulario venció. Recarga la página e inténtalo de nuevo.';
    $_SESSION['tipo_mensaje'] = 'error';
    header('Location: ' . $destino);
    exit;
}

asegurarTablaConsultorio($conexion);
$accion = trim((string) ($_POST['accion'] ?? ''));

try {
    // 1. CREAR NUEVO CONSULTORIO (RF-041)
    if ($accion === 'crear') {
        $nombre = trim(preg_replace('/\s+/u', ' ', (string) ($_POST['nombre'] ?? '')) ?? '');
        $longitud = function_exists('mb_strlen') ? mb_strlen($nombre, 'UTF-8') : strlen($nombre);

        if ($nombre === '' || $longitud < 2 || $longitud > 30) {
            $_SESSION['mensaje'] = 'El nombre del consultorio debe tener entre 2 y 30 caracteres.';
            $_SESSION['tipo_mensaje'] = 'error';
        } elseif (!preg_match('/^[\p{L}\p{N}\s\-\.#°\/]+$/u', $nombre)) {
            $_SESSION['mensaje'] = 'El nombre del consultorio contiene caracteres no permitidos.';
            $_SESSION['tipo_mensaje'] = 'error';
        } else {
            $stmtDup = $conexion->prepare(
                'SELECT id FROM consultorio WHERE lower(trim(nombre)) = lower(trim(:nombre)) LIMIT 1'
            );
            $stmtDup->execute([':nombre' => $nombre]);

            if ($stmtDup->fetch()) {
                $_SESSION['mensaje'] = "Ya existe un consultorio registrado con el nombre \"{$nombre}\".";
                $_SESSION['tipo_mensaje'] = 'error';
            } else {
                $stmtIns = $conexion->prepare('INSERT INTO consultorio (nombre) VALUES (:nombre)');
                $stmtIns->execute([':nombre' => $nombre]);

                $_SESSION['mensaje'] = "Consultorio \"{$nombre}\" creado correctamente y listo para asignar.";
                $_SESSION['tipo_mensaje'] = 'exito';
            }
        }

    // 2. ELIMINAR CONSULTORIO LIBRE (RF-041 / RN-20)
    } elseif ($accion === 'eliminar') {
        $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);

        if ($id === false || $id === null || $id <= 0) {
            $_SESSION['mensaje'] = 'Error: el ID del consultorio no es válido.';
            $_SESSION['tipo_mensaje'] = 'error';
        } else {
            $stmtRow = $conexion->prepare('SELECT id, nombre FROM consultorio WHERE id = :id LIMIT 1');
            $stmtRow->execute([':id' => $id]);
            $cons = $stmtRow->fetch(PDO::FETCH_ASSOC);

            if (!$cons) {
                $_SESSION['mensaje'] = 'No se encontró el consultorio solicitado.';
                $_SESSION['tipo_mensaje'] = 'error';
            } else {
                // RN-20: No se puede eliminar un consultorio que esté ocupado por un odontólogo activo
                $stmtOcu = $conexion->prepare(
                    "SELECT nombre
                     FROM odontologo
                     WHERE activo IS TRUE
                       AND consultorio IS NOT NULL
                       AND lower(trim(consultorio)) = lower(trim(:nombre))
                     LIMIT 1"
                );
                $stmtOcu->execute([':nombre' => $cons['nombre']]);
                $ocupante = $stmtOcu->fetchColumn();

                if ($ocupante) {
                    $_SESSION['mensaje'] = "No se puede eliminar \"{$cons['nombre']}\" porque está ocupado actualmente por {$ocupante} (RN-20). Reasigna o desactiva primero al odontólogo.";
                    $_SESSION['tipo_mensaje'] = 'error';
                } else {
                    $stmtDel = $conexion->prepare('DELETE FROM consultorio WHERE id = :id');
                    $stmtDel->execute([':id' => $id]);

                    $_SESSION['mensaje'] = "Consultorio \"{$cons['nombre']}\" eliminado correctamente.";
                    $_SESSION['tipo_mensaje'] = 'exito';
                }
            }
        }
    } else {
        $_SESSION['mensaje'] = 'Acción no reconocida para la gestión de consultorios.';
        $_SESSION['tipo_mensaje'] = 'error';
    }
} catch (PDOException $e) {
    error_log('Error en logica_consultorio.php: ' . $e->getMessage());
    if ((string) $e->getCode() === '23505') {
        $_SESSION['mensaje'] = 'Ya existe un consultorio con ese nombre en la sede.';
    } else {
        $_SESSION['mensaje'] = 'Ocurrió un error al procesar el consultorio. Inténtalo de nuevo.';
    }
    $_SESSION['tipo_mensaje'] = 'error';
}

header('Location: ' . $destino);
exit;

