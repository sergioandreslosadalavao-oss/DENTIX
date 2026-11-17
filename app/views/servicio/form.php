<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Servicio - SCO</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@500;600;700;800&family=Sora:wght@700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="<?php echo asset('css/admin.css'); ?>">
    <link rel="stylesheet" href="<?php echo asset('css/index.css'); ?>">
</head>
<body class="bg-light" style="display: grid; place-items: center; min-height: 100vh; margin: 0; font-family: 'Manrope', sans-serif;">

    <div class="container" style="max-width: 560px; padding: 20px;">
        <div class="card border-0 shadow-lg rounded-4 p-4 p-md-5 bg-white">
            
            <div class="text-center mb-4">
                <div class="d-inline-flex align-items-center justify-content-center text-white rounded-circle mb-3" style="width: 56px; height: 56px; background-color: #0d9488;">
                    <i class="fa-solid fa-notes-medical fs-4"></i>
                </div>
                <h3 class="fw-bold text-dark mb-1" style="font-family: 'Sora', sans-serif;">Registrar Servicio</h3>
                <p class="text-muted small">Ingrese la información completa del tratamiento u servicio odontológico</p>
            </div>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger py-2 small mb-4 border-0 shadow-sm d-flex align-items-center">
                    <i class="fa-solid fa-triangle-exclamation me-2"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form action="<?php echo url("servicios/procesar"); ?>" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
                
                <!-- Nombre del Servicio -->
                <div class="mb-3">
                    <label for="nombre" class="form-label small fw-semibold text-dark">Nombre del Servicio</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-tooth text-muted"></i></span>
                        <input type="text" class="form-control border-start-0" id="nombre" name="nombre" placeholder="Ej. Blanqueamiento dental" required>
                    </div>
                </div>

                <!-- Descripción Clínica (NUEVO CAMPO AÑADIDO) -->
                <div class="mb-3">
                    <label for="descripcion" class="form-label small fw-semibold text-dark">Descripción Clínica</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 align-items-start pt-2"><i class="fa-solid fa-align-left text-muted"></i></span>
                        <textarea class="form-control border-start-0" id="descripcion" name="descripcion" rows="3" placeholder="Ej. Tratamiento estético avanzado para aclarar el tono natural de los dientes..."></textarea>
                    </div>
                </div>

                <!-- Duración Promedio -->
                <div class="mb-3">
                    <label for="duracion_min" class="form-label small fw-semibold text-dark">Duración Promedio (minutos)</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-clock text-muted"></i></span>
                        <input type="number" class="form-control border-start-0" id="duracion_min" name="duracion_min" min="15" step="5" value="30" required>
                    </div>
                </div>

                <!-- Precio / Costo (COP) -->
                <div class="mb-4">
                    <label for="precio" class="form-label small fw-semibold text-dark">Costo / Precio (COP)</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-peso-sign text-muted"></i></span>
                        <input type="number" class="form-control border-start-0" id="precio" name="precio" min="0" step="500" value="0" required>
                    </div>
                    <small class="text-muted d-block mt-1" id="usdPreviewHint" data-cop-input-preview="#precio"></small>
                </div>

                <!-- Botón de Registro -->
                <button type="submit" class="btn text-white w-100 py-2 fw-bold rounded-pill mb-4 shadow-sm" style="background-color: #0d9488; border: none;">
                    <i class="fa-solid fa-plus-circle me-2"></i> Registrar Servicio
                </button>

                <!-- Enlaces de Navegación Inferiores -->
                <div class="d-flex justify-content-between align-items-center pt-3 border-top small">
                    <a href="<?php echo url("servicios"); ?>" class="text-primary fw-bold text-decoration-none">
                        <i class="fa-solid fa-list me-1"></i> Ver servicios registrados
                    </a>
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