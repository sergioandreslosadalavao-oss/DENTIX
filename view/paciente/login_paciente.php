<?php
require_once __DIR__ . '/../../app/paciente_auth.php';
iniciar_sesion_paciente();
require_once __DIR__ . '/../../config/database.php';

if (paciente_autenticado()) {
    header('Location: perfil_paciente.php');
    exit;
}

$next = ($_GET['next'] ?? $_POST['next'] ?? '') === 'portal_paciente.php'
    ? 'portal_paciente.php'
    : 'perfil_paciente.php';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cedula = is_string($_POST['cedula'] ?? null) ? trim($_POST['cedula']) : '';
    $password = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';

    if (!validar_csrf_paciente()) {
        $error = 'La sesión del formulario venció. Recarga la página e inténtalo de nuevo.';
    } elseif ($cedula === '' || $password === '') {
        $error = 'Ingresa tu cédula y contraseña.';
    } else {
        try {
            $stmt = $conexion->prepare(
                'SELECT id, nombre, password_hash FROM paciente WHERE cedula = ? LIMIT 1'
            );
            $stmt->execute([$cedula]);
            $paciente = $stmt->fetch();

            if ($paciente && !empty($paciente['password_hash']) && password_verify($password, $paciente['password_hash'])) {
                session_regenerate_id(true);
                $_SESSION['paciente_id'] = (string) $paciente['id'];
                $_SESSION['paciente_nombre'] = $paciente['nombre'];
                header('Location: ' . $next);
                exit;
            }

            $error = 'No pudimos iniciar sesión con esos datos. Verifica tu cédula y contraseña.';
        } catch (PDOException $e) {
            error_log('Error al iniciar sesión de paciente: ' . $e->getMessage());
            $error = 'No se pudo iniciar sesión. Inténtalo nuevamente más tarde.';
        }
    }
}

$csrfToken = token_csrf_paciente();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar sesión - DENTIX</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@500;600;700;800&family=Sora:wght@700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="../../assests/css/admin.css">
    <link rel="stylesheet" href="../../assests/css/index.css">
    <link rel="stylesheet" href="../../assests/css/paciente.css">
</head>
<body class="admin-body patient-auth-body">
    <main class="patient-auth-card">
        <a href="../../index.php" class="brand patient-auth-brand">
            <span class="brand__mark"><i class="fa-solid fa-tooth"></i></span>
            <span class="brand__text">DENTIX<small>Portal del paciente</small></span>
        </a>
        <div class="patient-auth-heading">
            <span class="patient-auth-icon"><i class="fa-solid fa-user-check"></i></span>
            <span class="eyebrow">Qué bueno tenerte de vuelta</span>
            <h1>Inicia sesión</h1>
            <p>Accede a tu perfil y consulta el estado de tus citas.</p>
        </div>

        <?php if ($error !== ''): ?>
            <div class="alert alert-danger py-2 small" role="alert">
                <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="login_paciente.php">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
            <input type="hidden" name="next" value="<?php echo htmlspecialchars($next, ENT_QUOTES, 'UTF-8'); ?>">
            <div class="mb-3">
                <label for="cedula" class="form-label small fw-bold">Número de cédula</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-id-card text-muted"></i></span>
                    <input type="text" class="form-control border-start-0" id="cedula" name="cedula" required autocomplete="username" inputmode="numeric">
                </div>
            </div>
            <div class="mb-4">
                <label for="password" class="form-label small fw-bold">Contraseña</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-lock text-muted"></i></span>
                    <input type="password" class="form-control border-start-0" id="password" name="password" required autocomplete="current-password">
                </div>
            </div>
            <button type="submit" class="btn btn--primary w-100 py-2 fw-bold">
                <i class="fa-solid fa-right-to-bracket me-2"></i> Iniciar sesión
            </button>
        </form>

        <div class="patient-auth-footer">
            <span>¿Aún no tienes una cuenta?</span>
            <a href="form_paciente.php">Regístrate aquí</a>
        </div>
        <a href="../../index.php" class="patient-auth-back"><i class="fa-solid fa-arrow-left"></i> Volver al inicio</a>
    </main>
</body>
</html>
