<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Odontólogo - DENTIX</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@500;600;700;800&family=Sora:wght@700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="<?php echo asset('css/admin.css'); ?>">
    <link rel="stylesheet" href="<?php echo asset('css/index.css'); ?>">
</head>
<body class="bg-light" style="display: grid; place-items: center; min-height: 100vh; margin: 0; font-family: 'Manrope', sans-serif;">

    <div class="container" style="max-width: 540px; padding: 20px;">
        <div class="card border-0 shadow-lg rounded-4 p-4 p-md-5 bg-white">
            
            <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
                <div>
                    <span class="badge bg-teal bg-opacity-10 text-teal px-3 py-1 rounded-pill fw-bold mb-2" style="background-color: #ccfbf1; color: #0d9488; font-size: 0.75rem;">
                        <i class="fa-solid fa-circle-dot me-1"></i> ESPECIALISTAS
                    </span>
                    <h3 class="fw-bold text-dark m-0" style="font-family: 'Sora', sans-serif;">Registrar Odontólogo</h3>
                </div>
                <a href="<?php echo url("odontologos"); ?>" class="text-muted text-decoration-none small fw-semibold">
                    <i class="fa-solid fa-arrow-left me-1"></i> Volver
                </a>
            </div>

            <?php if ($error !== ''): ?>
                <div class="alert alert-danger py-2 small mb-4 border-0 shadow-sm d-flex align-items-center">
                    <i class="fa-solid fa-triangle-exclamation me-2"></i>
                    <div><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
                </div>
            <?php endif; ?>

            <form action="<?php echo url("odontologos/procesar"); ?>" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
                
                <div class="mb-3">
                    <label for="nombre" class="form-label small fw-semibold text-dark">Nombre Completo</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-user-doctor text-muted"></i></span>
                        <input type="text" class="form-control border-start-0" id="nombre" name="nombre" maxlength="100" value="<?php echo htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8'); ?>" placeholder="Ej. Dr. Carlos Pérez" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="especialidad" class="form-label small fw-semibold text-dark">Especialidad</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-stethoscope text-muted"></i></span>
                        <input type="text" class="form-control border-start-0" id="especialidad" name="especialidad" maxlength="60" value="<?php echo htmlspecialchars($especialidad, ENT_QUOTES, 'UTF-8'); ?>" placeholder="Ej. Ortodoncia / Endodoncia" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="consultorio" class="form-label small fw-semibold text-dark">Consultorio</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-door-open text-muted"></i></span>
                        <input type="text" class="form-control border-start-0" id="consultorio" name="consultorio" maxlength="30" value="<?php echo htmlspecialchars($consultorio, ENT_QUOTES, 'UTF-8'); ?>" placeholder="Ej. Consultorio 302">
                    </div>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label small fw-semibold text-dark">Correo Electrónico (Login)</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-envelope text-muted"></i></span>
                        <input type="email" class="form-control border-start-0" id="email" name="email" maxlength="100" value="<?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?>" placeholder="carlos.perez@clinica.com" required>
                    </div>
                </div>

                <div class="mb-4">
                    <label for="password" class="form-label small fw-semibold text-dark">Contraseña de Acceso</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-lock text-muted"></i></span>
                        <input type="password" class="form-control border-start-0" id="password" name="password" minlength="6" maxlength="72" placeholder="••••••••" required>
                    </div>
                    <small class="text-muted">Mínimo 6 caracteres para ingresar al portal médico.</small>
                </div>

                <button type="submit" class="btn text-white w-100 py-2 fw-bold rounded-pill mb-4 shadow-sm" style="background-color: #0d9488; border: none;">
                    <i class="fa-solid fa-user-plus me-2"></i> Registrar Odontólogo
                </button>

                <div class="text-center pt-3 border-top small">
                    <a href="<?php echo url("odontologos"); ?>" class="text-primary fw-bold text-decoration-none d-inline-flex align-items-center gap-1 mb-2">
                        <i class="fa-solid fa-list"></i> Ver odontólogos registrados
                    </a>
                    <br>
                    <a href="<?php echo url("inicio"); ?>" class="text-muted text-decoration-none">
                        <i class="fa-solid fa-house me-1"></i> Volver al inicio
                    </a>
                </div>
            </form>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo asset('js/dentix-i18n.js?v=20261110-cur'); ?>"></script>
</body>
</html>
