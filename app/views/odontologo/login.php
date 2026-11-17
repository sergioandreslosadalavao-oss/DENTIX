<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso Cuerpo Médico - DENTIX</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@500;600;700;800&family=Sora:wght@700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="<?php echo asset('css/admin.css'); ?>">
    <link rel="stylesheet" href="<?php echo asset('css/index.css'); ?>">
</head>
<body class="admin-body" style="display: grid; place-items: center; min-height: 100vh; padding: 20px 0;">

    <div class="container" style="max-width: 480px;">
        <div class="form-card shadow-lg p-4 p-md-5 border-0 rounded-4 bg-white">
            
            <div class="text-center mb-4">
                <div class="brand__mark mx-auto mb-3" style="width: 56px; height: 56px; display: grid; place-items: center; background: linear-gradient(140deg, #0d9488, #0f766e); border-radius: 16px; color: white; font-size: 1.5rem; box-shadow: 0 8px 18px rgba(13, 148, 136, 0.28);">
                    <i class="fa-solid fa-user-doctor"></i>
                </div>
                <span class="eyebrow mb-1 text-primary fw-bold" style="font-size: 0.72rem; letter-spacing: 0.05em; text-transform: uppercase;">Portal Especialistas</span>
                <h2 class="fw-bold text-dark mb-1" style="font-family: 'Sora', sans-serif;">Acceso Odontólogos</h2>
                <p class="text-muted small">Ingresa con tu correo institucional para revisar tu agenda y finalizar citas</p>
            </div>

            <?php if ($error !== ''): ?>
                <div class="alert alert-danger py-2 mb-3 small d-flex align-items-center rounded-3" role="alert">
                    <i class="fa-solid fa-triangle-exclamation me-2 fs-6"></i>
                    <div><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
                </div>
            <?php endif; ?>

            <form action="<?php echo url("login-odontologo"); ?>" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">

                <div class="mb-3">
                    <label for="email" class="form-label fw-semibold small">Correo Institucional</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-envelope text-muted"></i></span>
                        <input type="email" class="form-control border-start-0" id="email" name="email" required placeholder="luis.mendoza@clinica.com" autocomplete="username">
                    </div>
                </div>

                <div class="mb-4">
                    <label for="password" class="form-label fw-semibold small">Contraseña</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-lock text-muted"></i></span>
                        <input type="password" class="form-control border-start-0" id="password" name="password" required placeholder="••••••••" autocomplete="current-password">
                    </div>
                </div>

                <div class="d-grid gap-2 mb-3">
                    <button type="submit" class="btn btn--primary py-2 fw-bold rounded-3">
                        <i class="fa-solid fa-right-to-bracket me-2"></i> Ingresar a mi Agenda
                    </button>
                </div>
            </form>

            <hr class="my-4 text-muted">

            <div class="d-flex justify-content-between align-items-center">
                <span class="text-muted small">Cuerpo Médico DENTIX</span>
                <a href="<?php echo url("inicio"); ?>" class="text-secondary small text-decoration-none fw-semibold">
                    <i class="fa-solid fa-arrow-left me-1"></i> Ir al Inicio
                </a>
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
    <script src="<?php echo asset('js/admin-animations.js'); ?>"></script>
    <script src="<?php echo asset('js/dentix-i18n.js?v=20261110-cur'); ?>"></script>
</body>
</html>

