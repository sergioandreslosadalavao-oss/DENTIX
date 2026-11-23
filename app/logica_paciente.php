<?php

$metodo = $_SERVER['REQUEST_METHOD'];
$accion = $_GET['accion'] ?? $_POST['accion'] ?? 'registrar';

// =========================================================================
// AUTOGESTIÓN DEL PERFIL POR EL PACIENTE TITULAR (RF-004 / RF-037 / Ley 1581)
// =========================================================================
if ($metodo === 'POST' && in_array($accion, ['actualizar_perfil_propio', 'actualizar_alergias'], true)) {
    require_once __DIR__ . '/paciente_auth.php';
    iniciar_sesion_paciente();

    if (!paciente_autenticado()) {
        header('Location: ../view/paciente/login_paciente.php');
        exit;
    }

    require_once __DIR__ . '/../config/database.php';

    if (!validar_csrf_paciente()) {
        $_SESSION['paciente_error'] = 'La sesión del formulario venció. Recarga la página e inténtalo nuevamente.';
        header('Location: ../view/paciente/perfil_paciente.php');
        exit;
    }

    $pacienteId = (int) $_SESSION['paciente_id'];

    $catalogoAlergias = [
        'Ninguna registrada' => 'Ninguna alergia conocida',
        'Penicilina / Amoxicilina (Antibióticos)' => 'Penicilina / Amoxicilina (Antibióticos)',
        'Anestesia local (Lidocaína / Articaína)' => 'Anestesia local (Lidocaína / Articaína)',
        'Ibuprofeno / AINEs / Aspirina (Analgésicos)' => 'Ibuprofeno / AINEs / Aspirina (Analgésicos)',
        'Látex (Guantes o insumos)' => 'Látex (Guantes o insumos)',
        'Yodo / Clorhexidina (Antisépticos)' => 'Yodo / Clorhexidina (Antisépticos)',
        'Metales dentales (Níquel / Cromo / Cobalto)' => 'Metales dentales (Níquel / Cromo / Cobalto)',
        'Otra' => 'Otra alergia (Escribir manualmente...)',
    ];

    $resolverAlergias = static function (string $select, string $manual) use ($catalogoAlergias): array {
        if (!array_key_exists($select, $catalogoAlergias)) {
            $select = 'Otra';
        }
        if ($select === 'Otra' && $manual === '') {
            return [false, 'Por favor escribe cuál es tu alergia en el campo de texto.'];
        }
        if ($select === 'Otra') {
            $final = $manual;
        } elseif ($manual !== '' && $select !== 'Ninguna registrada') {
            $final = $select . ' - ' . $manual;
        } elseif ($manual !== '' && $select === 'Ninguna registrada') {
            $final = $manual;
        } else {
            $final = $select !== '' ? $select : 'Ninguna registrada';
        }
        return [true, mb_substr($final, 0, 120, 'UTF-8')];
    };

    if ($accion === 'actualizar_alergias') {
        $alergiaSelect = is_string($_POST['alergia_select'] ?? null) ? trim($_POST['alergia_select']) : 'Ninguna registrada';
        $alergiaManual = is_string($_POST['alergia_manual'] ?? null) ? trim($_POST['alergia_manual']) : '';
        [$okAlergia, $alergiasFinal] = $resolverAlergias($alergiaSelect, $alergiaManual);

        if (!$okAlergia) {
            $_SESSION['paciente_error'] = $alergiasFinal;
            header('Location: ../view/paciente/perfil_paciente.php');
            exit;
        }

        try {
            $stmtUpd = $conexion->prepare('UPDATE paciente SET alergias = :alergias WHERE id = :id');
            $stmtUpd->execute([':alergias' => $alergiasFinal, ':id' => $pacienteId]);
            $_SESSION['paciente_flash'] = 'Tus alergias y alertas médicas fueron actualizadas correctamente.';
        } catch (PDOException $e) {
            error_log('Error al actualizar alergias del paciente: ' . $e->getMessage());
            $_SESSION['paciente_error'] = 'No se pudieron actualizar tus alergias en este momento. Inténtalo nuevamente.';
        }

        header('Location: ../view/paciente/perfil_paciente.php');
        exit;
    }

    // Acción: actualizar_perfil_propio (Nombre, Teléfono, Correo, Fecha de nacimiento, Alergias y Contraseña opcional)
    $nombre = is_string($_POST['nombre'] ?? null) ? trim($_POST['nombre']) : '';
    $telefono = is_string($_POST['telefono'] ?? null) ? trim($_POST['telefono']) : '';
    $email = is_string($_POST['email'] ?? null) ? trim($_POST['email']) : '';
    $fecha_nac = is_string($_POST['fecha_nac'] ?? null) ? trim($_POST['fecha_nac']) : '';
    $alergiaSelect = is_string($_POST['alergia_select'] ?? null) ? trim($_POST['alergia_select']) : 'Ninguna registrada';
    $alergiaManual = is_string($_POST['alergia_manual'] ?? null) ? trim($_POST['alergia_manual']) : '';
    $nuevaPass = is_string($_POST['nueva_password'] ?? null) ? $_POST['nueva_password'] : '';
    $confirmarPass = is_string($_POST['confirmar_password'] ?? null) ? $_POST['confirmar_password'] : '';

    if ($nombre === '' || $telefono === '') {
        $_SESSION['paciente_error'] = 'El nombre completo y el teléfono de contacto son obligatorios.';
        header('Location: ../view/paciente/perfil_paciente.php?editar=1');
        exit;
    }

    $nombre = preg_replace('/\s+/u', ' ', $nombre) ?? $nombre;
    $nombre = preg_replace_callback(
        '/(?<=\s)(De|Del|La|Las|Los|Y|Da|Do|Dos)(?=\s)/u',
        static fn(array $m): string => mb_strtolower($m[1], 'UTF-8'),
        $nombre
    ) ?? $nombre;

    $patronPalabra = "(?:Mc|Mac|[A-ZÁÉÍÓÚÜÑ]['-])?[A-ZÁÉÍÓÚÜÑ][a-záéíóúüñ]+(?:-[A-ZÁÉÍÓÚÜÑ][a-záéíóúüñ]+)*";
    if (preg_match('/^' . $patronPalabra . '(?: (?:(?:de|del|la|las|los|y|da|do|dos) )*' . $patronPalabra . ')*$/u', $nombre) !== 1) {
        $_SESSION['paciente_error'] = 'Cada palabra principal del nombre debe empezar con mayúscula seguida de minúsculas (ejemplo: Andrea Morales o María de la Cruz).';
        header('Location: ../view/paciente/perfil_paciente.php?editar=1');
        exit;
    }

    if (strlen(preg_replace('/\D+/', '', $telefono) ?? '') < 7) {
        $_SESSION['paciente_error'] = 'Ingresa un número de teléfono válido (mínimo 7 dígitos).';
        header('Location: ../view/paciente/perfil_paciente.php?editar=1');
        exit;
    }

    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['paciente_error'] = 'El correo electrónico ingresado no tiene un formato válido.';
        header('Location: ../view/paciente/perfil_paciente.php?editar=1');
        exit;
    }

    if ($fecha_nac !== '' && $fecha_nac > date('Y-m-d')) {
        $_SESSION['paciente_error'] = 'La fecha de nacimiento no puede ser una fecha futura.';
        header('Location: ../view/paciente/perfil_paciente.php?editar=1');
        exit;
    }

    [$okAlergia, $alergiasFinal] = $resolverAlergias($alergiaSelect, $alergiaManual);
    if (!$okAlergia) {
        $_SESSION['paciente_error'] = $alergiasFinal;
        header('Location: ../view/paciente/perfil_paciente.php?editar=1');
        exit;
    }

    $actualizarClave = ($nuevaPass !== '' || $confirmarPass !== '');
    if ($actualizarClave) {
        if (strlen($nuevaPass) < 8 || strlen($nuevaPass) > 72) {
            $_SESSION['paciente_error'] = 'La nueva contraseña debe tener entre 8 y 72 caracteres.';
            header('Location: ../view/paciente/perfil_paciente.php?editar=1');
            exit;
        }
        if (!hash_equals($nuevaPass, $confirmarPass)) {
            $_SESSION['paciente_error'] = 'La nueva contraseña y su confirmación no coinciden.';
            header('Location: ../view/paciente/perfil_paciente.php?editar=1');
            exit;
        }
    }

    try {
        if ($actualizarClave) {
            $stmtUpd = $conexion->prepare(
                'UPDATE paciente
                 SET nombre = :nombre,
                     telefono = :telefono,
                     email = :email,
                     fecha_nac = :fecha_nac,
                     alergias = :alergias,
                     password_hash = :password_hash
                 WHERE id = :id'
            );
            $stmtUpd->execute([
                ':nombre' => $nombre,
                ':telefono' => $telefono,
                ':email' => $email === '' ? null : $email,
                ':fecha_nac' => $fecha_nac === '' ? null : $fecha_nac,
                ':alergias' => $alergiasFinal,
                ':password_hash' => password_hash($nuevaPass, PASSWORD_DEFAULT),
                ':id' => $pacienteId,
            ]);
        } else {
            $stmtUpd = $conexion->prepare(
                'UPDATE paciente
                 SET nombre = :nombre,
                     telefono = :telefono,
                     email = :email,
                     fecha_nac = :fecha_nac,
                     alergias = :alergias
                 WHERE id = :id'
            );
            $stmtUpd->execute([
                ':nombre' => $nombre,
                ':telefono' => $telefono,
                ':email' => $email === '' ? null : $email,
                ':fecha_nac' => $fecha_nac === '' ? null : $fecha_nac,
                ':alergias' => $alergiasFinal,
                ':id' => $pacienteId,
            ]);
        }

        $_SESSION['paciente_nombre'] = $nombre;
        $_SESSION['paciente_flash'] = $actualizarClave
            ? 'Tus datos personales y tu contraseña fueron actualizados y sincronizados con la clínica.'
            : 'Tus datos personales fueron actualizados correctamente y ya están sincronizados con recepción.';
    } catch (PDOException $e) {
        error_log('Error al actualizar perfil propio del paciente: ' . $e->getMessage());
        $_SESSION['paciente_error'] = 'No pudimos guardar tus cambios en este momento. Verifica el formato de tus datos e inténtalo de nuevo.';
        header('Location: ../view/paciente/perfil_paciente.php?editar=1');
        exit;
    }

    header('Location: ../view/paciente/perfil_paciente.php');
    exit;
}

require_once __DIR__ . '/recepcionista_auth.php';
requerir_recepcionista('../view/recepcionista/lista_recepcionista.php');
require_once __DIR__ . '/../config/database.php';

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

// RF-042 / Ley 1581: Recepción no tiene permisos para editar pacientes; la edición es exclusiva del titular desde su Portal
if ($metodo === 'GET' && $accion === 'editar') {
    $_SESSION['mensaje'] = 'Recepción no tiene permisos para editar expedientes de pacientes. Cada paciente actualiza sus propios datos desde su Portal (Ley 1581 / RF-042).';
    $_SESSION['tipo_mensaje'] = 'error';
    header('Location: ../view/paciente/lista_pacientes.php');
    exit;
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
    $_SESSION['mensaje'] = 'Recepción no tiene permisos para editar expedientes de pacientes. Cada paciente actualiza sus propios datos desde su Portal (Ley 1581 / RF-042).';
    $_SESSION['tipo_mensaje'] = 'error';
    header('Location: ../view/paciente/lista_pacientes.php');
    exit;
}

if ($accion === 'eliminar') {
    $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);

    if ($id === false || $id === null || $id <= 0) {
        $_SESSION['mensaje'] = 'Error: el ID del paciente no es válido.';
        $_SESSION['tipo_mensaje'] = 'error';
    } else {
        try {
            $stmt = $conexion->prepare('DELETE FROM paciente WHERE id = :id');
            $stmt->execute([':id' => $id]);

            if ($stmt->rowCount() > 0) {
                $_SESSION['mensaje'] = 'Paciente eliminado correctamente.';
                $_SESSION['tipo_mensaje'] = 'exito';
            } else {
                $_SESSION['mensaje'] = 'No se encontró el paciente que intentas eliminar.';
                $_SESSION['tipo_mensaje'] = 'error';
            }
        } catch (PDOException $e) {
            error_log('Error al eliminar paciente: ' . $e->getMessage());
            if ($e->getCode() === '23503') {
                $_SESSION['mensaje'] = 'No se puede eliminar el paciente porque tiene citas o historial clínico registrado (Resolución 1995 de 1999).';
            } else {
                $_SESSION['mensaje'] = 'No se pudo eliminar el paciente. Verifica las restricciones de la base de datos.';
            }
            $_SESSION['tipo_mensaje'] = 'error';
        }
    }

    header('Location: ../view/paciente/lista_pacientes.php');
    exit;
}

// Por seguridad y cumplimiento de la Ley 1581 de 2012, Recepción no crea cuentas de pacientes
$_SESSION['mensaje'] = 'Por seguridad y consentimiento personal (Ley 1581 de 2012), el registro de pacientes lo realiza únicamente el titular desde el portal público.';
$_SESSION['tipo_mensaje'] = 'error';
header('Location: ../view/paciente/lista_pacientes.php');
exit;
