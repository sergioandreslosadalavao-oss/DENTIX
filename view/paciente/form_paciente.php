<?php
// form_paciente.php - Registro de Nuevo Paciente
require_once __DIR__ . '/../../app/paciente_auth.php';
iniciar_sesion_paciente();
require_once __DIR__ . '/../../config/database.php';

if (paciente_autenticado()) {
    header('Location: perfil_paciente.php');
    exit;
}

$error = '';
$csrfToken = token_csrf_paciente();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = is_string($_POST['nombre'] ?? null) ? trim($_POST['nombre']) : '';
    $cedula = is_string($_POST['cedula'] ?? null) ? trim($_POST['cedula']) : '';
    $telefono = is_string($_POST['telefono'] ?? null) ? trim($_POST['telefono']) : '';
    $correo = is_string($_POST['correo'] ?? null) ? trim($_POST['correo']) : '';
    $password = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';
    $passwordConfirmacion = is_string($_POST['password_confirmacion'] ?? null)
        ? $_POST['password_confirmacion']
        : '';

    if (!validar_csrf_paciente()) {
        $error = 'La sesión del formulario venció. Recarga la página e inténtalo de nuevo.';
    } elseif (
        $nombre === ''
        || strlen($nombre) > 100
        || $cedula === ''
        || strlen($cedula) > 30
        || $telefono === ''
        || strlen($telefono) > 20
        || !filter_var($correo, FILTER_VALIDATE_EMAIL)
        || strlen($correo) > 100
        || $password === ''
    ) {
        $error = 'Completa todos los campos para crear tu cuenta.';
    } elseif (strlen($password) < 8 || strlen($password) > 72) {
        $error = 'La contraseña debe tener entre 8 y 72 caracteres.';
    } elseif ($password !== $passwordConfirmacion) {
        $error = 'Las contraseñas no coinciden.';
    } else {
        try {
            $check = $conexion->prepare(
                'SELECT id, nombre, telefono, email, password_hash
                 FROM paciente
                 WHERE cedula = ?
                 LIMIT 1'
            );
            $check->execute([$cedula]);

            $pacienteExistente = $check->fetch();
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);

            if ($pacienteExistente && !empty($pacienteExistente['password_hash'])) {
                $error = 'Ya existe una cuenta con esa cédula. Inicia sesión.';
            } elseif ($pacienteExistente) {
                $datosCoinciden = hash_equals((string) $pacienteExistente['telefono'], $telefono)
                    && strtolower((string) $pacienteExistente['email']) === strtolower($correo)
                    && mb_strtolower((string) $pacienteExistente['nombre'], 'UTF-8') === mb_strtolower($nombre, 'UTF-8');

                if (!$datosCoinciden) {
                    $error = 'No pudimos verificar tus datos. Revisa el teléfono y correo registrados o comunícate con recepción.';
                } else {
                    $stmt = $conexion->prepare(
                        'UPDATE paciente SET password_hash = ? WHERE id = ? AND password_hash IS NULL'
                    );
                    $stmt->execute([$passwordHash, $pacienteExistente['id']]);
                    if ($stmt->rowCount() === 1) {
                        $pacienteId = $pacienteExistente['id'];
                    } else {
                        $error = 'La cuenta ya fue activada. Inicia sesión con tu cédula y contraseña.';
                    }
                }
            } else {
                $stmt = $conexion->prepare(
                    'INSERT INTO paciente (nombre, cedula, telefono, email, password_hash)
                     VALUES (?, ?, ?, ?, ?)
                     RETURNING id'
                );
                $stmt->execute([$nombre, $cedula, $telefono, $correo, $passwordHash]);
                $pacienteId = $stmt->fetchColumn();
            }

            if ($error === '') {
                session_regenerate_id(true);
                $_SESSION['paciente_id'] = (string) $pacienteId;
                $_SESSION['paciente_nombre'] = $nombre;
                header('Location: perfil_paciente.php');
                exit;
            }
        } catch (PDOException $e) {
            error_log('Error al registrar cuenta de paciente: ' . $e->getMessage());
            $error = 'No se pudo crear tu cuenta. Verifica los datos e inténtalo nuevamente.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Nuevo Paciente - DENTIX</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@500;600;700;800&family=Sora:wght@700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    
    <link rel="stylesheet" href="../../assests/css/admin.css">
    <link rel="stylesheet" href="../../assests/css/index.css">
    <link rel="stylesheet" href="../../assests/css/paciente.css">
</head>
<body class="admin-body" style="display: grid; place-items: center; min-height: 100vh; padding: 24px 0;">

    <div class="container" style="max-width: 520px;">
        <div class="form-card shadow-lg p-4 p-md-5 border-0 rounded-4">
            
            <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
                <div>
                    <span class="eyebrow mb-2">Portal del paciente</span>
                    <h2 class="fw-bold text-dark mb-0 fs-3">
                        <i class="fa-solid fa-user-plus text-primary me-2"></i> Crear mi cuenta
                    </h2>
                </div>
                <a href="../../index.php" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                    <i class="fa-solid fa-arrow-left me-1"></i> Volver
                </a>
            </div>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger py-2 mb-4 small d-flex align-items-center" role="alert">
                    <i class="fa-solid fa-triangle-exclamation me-2 fs-5"></i>
                    <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
                </div>
            <?php endif; ?>

            <form action="form_paciente.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
                <div class="mb-3">
                    <label for="nombre" class="form-label fw-semibold small">Nombres y Apellidos</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-user text-muted"></i></span>
                        <input type="text" class="form-control border-start-0" id="nombre" name="nombre" required maxlength="100" autocomplete="name" placeholder="Ej. Juan Pérez">
                    </div>
                </div>

                <div class="mb-3">
                    <label for="cedula" class="form-label fw-semibold small">Número de Cédula</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-id-card text-muted"></i></span>
                        <input type="text" class="form-control border-start-0" id="cedula" name="cedula" required maxlength="30" autocomplete="username" inputmode="numeric" placeholder="Ej. 123456789">
                    </div>
                </div>

                <div class="mb-3">
                    <label for="telefono" class="form-label fw-semibold small">Teléfono / Celular</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-phone text-muted"></i></span>
                        <input type="text" class="form-control border-start-0" id="telefono" name="telefono" required maxlength="20" autocomplete="tel" placeholder="Ej. 3001234567">
                    </div>
                </div>

                <div class="mb-4">
                    <label for="correo" class="form-label fw-semibold small">Correo Electrónico</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-envelope text-muted"></i></span>
                        <input type="email" class="form-control border-start-0" id="correo" name="correo" required maxlength="100" autocomplete="email" placeholder="correo@ejemplo.com">
                    </div>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label fw-semibold small">Crea una contraseña</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-lock text-muted"></i></span>
                        <input type="password" class="form-control border-start-0" id="password" name="password" required minlength="8" maxlength="72" autocomplete="new-password">
                    </div>
                    <small class="text-muted">Usa al menos 8 caracteres.</small>
                </div>

                <div class="mb-4">
                    <label for="password_confirmacion" class="form-label fw-semibold small">Confirma tu contraseña</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-shield-halved text-muted"></i></span>
                        <input type="password" class="form-control border-start-0" id="password_confirmacion" name="password_confirmacion" required minlength="8" maxlength="72" autocomplete="new-password">
                    </div>
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn--primary py-2 fw-bold">
                        <i class="fa-solid fa-user-check me-2"></i> Crear cuenta
                    </button>
                </div>
            </form>

            <div class="text-center mt-4 pt-3 border-top small">
                <span class="text-muted">¿Ya tienes una cuenta?</span>
                <a href="login_paciente.php" class="fw-bold text-primary text-decoration-none ms-1">Inicia sesión</a>
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
    <script src="../../assests/js/admin-animations.js"></script>
</body>
</html>