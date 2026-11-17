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
    <link rel="stylesheet" href="<?php echo asset('css/admin.css'); ?>">
    <link rel="stylesheet" href="<?php echo asset('css/index.css'); ?>">
    <link rel="stylesheet" href="<?php echo asset('css/paciente.css?v=20261110-vivid'); ?>">
</head>
<body class="admin-body patient-auth-body">
    <main class="patient-auth-card">
        <a href="<?php echo url("inicio"); ?>" class="brand patient-auth-brand">
            <span class="brand__mark"><i class="fa-solid fa-tooth"></i></span>
            <span class="brand__text">DENTIX<small>Portal del paciente</small></span>
        </a>
        <div class="patient-auth-heading">
            <span class="eyebrow">Qué bueno tenerte de vuelta</span>
            <h1>Inicia sesión</h1>
            <p>Accede a tu perfil y consulta el estado de tus citas.</p>
        </div>

        <?php if ($error !== ''): ?>
            <div class="alert alert-danger py-2 small" role="alert">
                <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?php echo url("login-paciente"); ?>">
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
            <a href="<?php echo url("registro-paciente"); ?>">Regístrate aquí</a>
        </div>
        <a href="<?php echo url("inicio"); ?>" class="patient-auth-back"><i class="fa-solid fa-arrow-left"></i> Volver al inicio</a>
    </main>
    <script src="<?php echo asset('js/dentix-i18n.js?v=20261110-cur'); ?>"></script>
</body>
</html>
