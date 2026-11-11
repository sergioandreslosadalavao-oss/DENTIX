<?php

require_once __DIR__ . '/recepcionista_auth.php';
requerir_recepcionista('../view/recepcionista/lista_recepcionista.php');
require_once __DIR__ . '/../config/database.php';

$metodo = $_SERVER['REQUEST_METHOD'];
$accion = $_GET['accion'] ?? $_POST['accion'] ?? 'registrar';

class LogicaPaciente
{
    private PDO $conn;

    public function __construct(?PDO $conexion = null)
    {
        if ($conexion !== null) {
            $this->conn = $conexion;
        } else {
            global $conexion;
            $this->conn = $conexion;
        }
    }

    public function registrarPaciente(
        string $nombre,
        string $telefono,
        string $email = '',
        string $fecha_nac = '',
        string $alergias = '',
        string $cedula = '',
        ?string $passwordHash = null
    ): bool {
        try {
            $sql = 'INSERT INTO paciente (nombre, cedula, telefono, email, password_hash, fecha_nac, alergias)
                    VALUES (:nombre, :cedula, :telefono, :email, :password_hash, :fecha_nac, :alergias)';
            $stmt = $this->conn->prepare($sql);

            return $stmt->execute([
                ':nombre' => trim($nombre),
                ':cedula' => trim($cedula) !== '' ? trim($cedula) : null,
                ':telefono' => trim($telefono),
                ':email' => trim($email) !== '' ? trim($email) : null,
                ':password_hash' => $passwordHash,
                ':fecha_nac' => trim($fecha_nac) !== '' ? trim($fecha_nac) : null,
                ':alergias' => trim($alergias) !== '' ? trim($alergias) : null,
            ]);
        } catch (PDOException $e) {
            error_log('Error al registrar paciente: ' . $e->getMessage());
            return false;
        }
    }
}

// CONSULTAR PACIENTE PARA EDITAR
if ($metodo === 'GET' && $accion === 'editar') {
    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

    if ($id === false || $id === null || $id <= 0) {
        $_SESSION['mensaje'] = 'Error: el ID del paciente no es válido.';
        $_SESSION['tipo_mensaje'] = 'error';
        header('Location: ../view/paciente/lista_pacientes.php');
        exit;
    }

    try {
        $stmt = $conexion->prepare(
            'SELECT id, nombre, cedula, telefono, email, fecha_nac, alergias
             FROM paciente
             WHERE id = :id'
        );
        $stmt->execute([':id' => $id]);
        $paciente = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$paciente) {
            $_SESSION['mensaje'] = 'No se encontró el paciente solicitado.';
            $_SESSION['tipo_mensaje'] = 'error';
            header('Location: ../view/paciente/lista_pacientes.php');
            exit;
        }

        require __DIR__ . '/../view/paciente/editar_paciente.php';
        exit;
    } catch (PDOException $e) {
        error_log('Error al consultar paciente: ' . $e->getMessage());
        $_SESSION['mensaje'] = 'No se pudieron consultar los datos del paciente.';
        $_SESSION['tipo_mensaje'] = 'error';
        header('Location: ../view/paciente/lista_pacientes.php');
        exit;
    }
}

if ($metodo !== 'POST') {
    header('Location: ../view/paciente/lista_pacientes.php');
    exit;
}

if (!validar_csrf_recepcionista()) {
    $_SESSION['mensaje'] = 'La sesión del formulario venció. Recarga la página e inténtalo de nuevo.';
    $_SESSION['tipo_mensaje'] = 'error';
    header('Location: ../view/paciente/lista_pacientes.php');
    exit;
}

if ($accion === 'actualizar') {
    $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
    $nombre = trim($_POST['nombre'] ?? '');
    $cedula = trim($_POST['cedula'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $fecha_nac = trim($_POST['fecha_nac'] ?? '');
    $alergias = trim($_POST['alergias'] ?? '');

    if ($id === false || $id === null || $id <= 0) {
        $_SESSION['mensaje'] = 'Error: el ID del paciente no es válido.';
        $_SESSION['tipo_mensaje'] = 'error';
        header('Location: ../view/paciente/lista_pacientes.php');
        exit;
    }

    if ($nombre === '' || $cedula === '' || $telefono === '') {
        $_SESSION['mensaje'] = 'Nombre, cédula y teléfono son obligatorios.';
        $_SESSION['tipo_mensaje'] = 'error';
        header('Location: ../view/paciente/editar_paciente.php?id=' . $id);
        exit;
    }

    if (preg_match('/^[A-ZÁÉÍÓÚÜÑ][a-záéíóúüñ]+(?: [A-ZÁÉÍÓÚÜÑ][a-záéíóúüñ]+)*$/u', $nombre) !== 1) {
        $_SESSION['mensaje'] = 'Cada palabra del nombre debe empezar con mayúscula seguida de minúsculas (ejemplo: Juan Steven).';
        $_SESSION['tipo_mensaje'] = 'error';
        header('Location: ../view/paciente/editar_paciente.php?id=' . $id);
        exit;
    }

    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['mensaje'] = 'El correo electrónico ingresado no es válido.';
        $_SESSION['tipo_mensaje'] = 'error';
        header('Location: ../view/paciente/editar_paciente.php?id=' . $id);
        exit;
    }

    if ($fecha_nac !== '' && $fecha_nac > date('Y-m-d')) {
        $_SESSION['mensaje'] = 'La fecha de nacimiento no puede ser futura.';
        $_SESSION['tipo_mensaje'] = 'error';
        header('Location: ../view/paciente/editar_paciente.php?id=' . $id);
        exit;
    }

    try {
        $dup = $conexion->prepare('SELECT id FROM paciente WHERE cedula = :cedula AND id <> :id LIMIT 1');
        $dup->execute([':cedula' => $cedula, ':id' => $id]);

        if ($dup->fetch()) {
            $_SESSION['mensaje'] = 'Ya existe otro paciente registrado con esa cédula.';
            $_SESSION['tipo_mensaje'] = 'error';
            header('Location: ../view/paciente/editar_paciente.php?id=' . $id);
            exit;
        }

        $stmt = $conexion->prepare(
            'UPDATE paciente
             SET nombre = :nombre,
                 cedula = :cedula,
                 telefono = :telefono,
                 email = :email,
                 fecha_nac = :fecha_nac,
                 alergias = :alergias
             WHERE id = :id'
        );
        $stmt->execute([
            ':nombre' => $nombre,
            ':cedula' => $cedula,
            ':telefono' => $telefono,
            ':email' => $email === '' ? null : $email,
            ':fecha_nac' => $fecha_nac === '' ? null : $fecha_nac,
            ':alergias' => $alergias === '' ? null : $alergias,
            ':id' => $id,
        ]);

        $verificacion = $conexion->prepare('SELECT id FROM paciente WHERE id = :id');
        $verificacion->execute([':id' => $id]);

        if ($verificacion->fetch()) {
            $_SESSION['mensaje'] = 'Paciente actualizado correctamente.';
            $_SESSION['tipo_mensaje'] = 'exito';
        } else {
            $_SESSION['mensaje'] = 'No se encontró el paciente que intentas actualizar.';
            $_SESSION['tipo_mensaje'] = 'error';
        }
    } catch (PDOException $e) {
        error_log('Error al actualizar paciente: ' . $e->getMessage());
        $_SESSION['mensaje'] = 'No se pudo actualizar el paciente. Verifica los datos e inténtalo nuevamente.';
        $_SESSION['tipo_mensaje'] = 'error';
        header('Location: ../view/paciente/editar_paciente.php?id=' . $id);
        exit;
    }

    header('Location: ../view/paciente/lista_pacientes.php');
    exit;
}

// Registrar nuevo paciente (si se envía con accion=registrar)
$nombre = trim($_POST['nombre'] ?? '');
$cedula = trim($_POST['cedula'] ?? '');
$telefono = trim($_POST['telefono'] ?? '');
$email = trim($_POST['email'] ?? '');
$fecha_nac = trim($_POST['fecha_nac'] ?? '');
$alergias = trim($_POST['alergias'] ?? '');
$password = $_POST['password'] ?? '';

if ($nombre !== '' && $cedula !== '' && $telefono !== '') {
    $logica = new LogicaPaciente($conexion);
    $passwordHash = $password !== '' ? password_hash($password, PASSWORD_DEFAULT) : null;
    $resultado = $logica->registrarPaciente($nombre, $telefono, $email, $fecha_nac, $alergias, $cedula, $passwordHash);

    if ($resultado) {
        $_SESSION['mensaje'] = 'Paciente registrado correctamente.';
        $_SESSION['tipo_mensaje'] = 'exito';
    } else {
        $_SESSION['mensaje'] = 'No se pudo registrar el paciente (verifica que la cédula no esté duplicada).';
        $_SESSION['tipo_mensaje'] = 'error';
    }
} else {
    $_SESSION['mensaje'] = 'Completa los campos obligatorios (Nombre, Cédula y Teléfono).';
    $_SESSION['tipo_mensaje'] = 'error';
}

header('Location: ../view/paciente/lista_pacientes.php');
exit;
