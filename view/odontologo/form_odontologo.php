<?php
// form_odontologo.php - Registro de Nuevo Odontólogo con diseño unificado
require_once "../../config/database.php";

$mensaje = "";
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $especialidad = trim($_POST['especialidad'] ?? '');
    $consultorio = trim($_POST['consultorio'] ?? '');

    if (!empty($nombre) && !empty($especialidad) && !empty($consultorio)) {
        try {
            $stmt = $conexion->prepare("INSERT INTO odontologo (nombre, especialidad, consultorio, estado) VALUES (?, ?, ?, 'activo')");
            $stmt->execute([$nombre, $especialidad, $consultorio]);

            $mensaje = "¡Odontólogo registrado con éxito en el sistema!";
        } catch (PDOException $e) {
            $error = "Error al guardar el odontólogo: " . $e->getMessage();
        }
    } else {
        $error = "Por favor, completa todos los campos del formulario.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Odontólogo - DENTIX</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@500;600;700;800&family=Sora:wght@700;800&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome para Iconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    
    <!-- ESTILOS GLOBALES DE LA CARPETA ASSESTS -->
    <link rel="stylesheet" href="../../assests/css/admin.css">
    <link rel="stylesheet" href="../../assests/css/index.css">
</head>
<body class="admin-body" style="display: grid; place-items: center; min-height: 100vh; padding: 24px 0; background-color: #f4f7fa;">

    <div class="container" style="max-width: 520px;">
        <div class="form-card shadow-lg p-4 p-md-5 border-0 rounded-4 bg-white">
            
            <!-- Encabezado del Formulario -->
            <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
                <div>
                    <span class="eyebrow mb-1 text-primary fw-bold" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px;">Especialistas</span>
                    <h2 class="fw-bold text-dark mb-0 fs-3">
                        <i class="fa-solid fa-user-doctor text-primary me-2"></i> Registrar Odontólogo
                    </h2>
                </div>
                <a href="../../index.php" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                    <i class="fa-solid fa-arrow-left me-1"></i> Inicio
                </a>
            </div>

            <!-- Alertas de éxito o error -->
            <?php if (!empty($mensaje)): ?>
                <div class="alert alert-success py-2 mb-4 small d-flex align-items-center border-0 shadow-sm" role="alert">
                    <i class="fa-solid fa-circle-check me-2 fs-5 text-success"></i> 
                    <div>
                        <?php echo $mensaje; ?>
                        <div class="mt-1"><a href="lista_odontologos.php" class="fw-bold text-success text-decoration-underline">Ver listado de odontólogos</a></div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger py-2 mb-4 small d-flex align-items-center border-0 shadow-sm" role="alert">
                    <i class="fa-solid fa-triangle-exclamation me-2 fs-5 text-danger"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <!-- Formulario de Registro -->
            <form action="form_odontologo.php" method="POST">
                <div class="mb-3">
                    <label for="nombre" class="form-label fw-semibold small text-dark">Nombre Completo</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-user-doctor text-muted"></i></span>
                        <input type="text" class="form-control border-start-0 py-2" id="nombre" name="nombre" required placeholder="Ej. Dr. Carlos Pérez">
                    </div>
                </div>

                <div class="mb-3">
                    <label for="especialidad" class="form-label fw-semibold small text-dark">Especialidad</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-stethoscope text-muted"></i></span>
                        <input type="text" class="form-control border-start-0 py-2" id="especialidad" name="especialidad" required placeholder="Ej. Ortodoncia / Endodoncia">
                    </div>
                </div>

                <div class="mb-4">
                    <label for="consultorio" class="form-label fw-semibold small text-dark">Consultorio</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-door-open text-muted"></i></span>
                        <input type="text" class="form-control border-start-0 py-2" id="consultorio" name="consultorio" required placeholder="Ej. Consultorio 302">
                    </div>
                </div>

                <div class="d-grid gap-2 mb-3">
                    <button type="submit" class="btn btn-primary py-2 fw-bold text-white rounded-pill" style="background: #0d9488; border: none;">
                        <i class="fa-solid fa-user-plus me-2"></i> Registrar Odontólogo
                    </button>
                </div>
            </form>

            <!-- Botones de navegación inferior -->
            <div class="d-flex flex-column gap-2 mt-4 pt-3 border-top">
                <a href="lista_odontologos.php" class="btn btn-outline-primary btn-sm py-2 rounded-pill fw-semibold">
                    <i class="fa-solid fa-list me-1"></i> Ver odontólogos registrados
                </a>
                <a href="../../index.php" class="btn btn-light btn-sm py-2 rounded-pill text-muted fw-semibold">
                    <i class="fa-solid fa-house me-1"></i> Volver al inicio
                </a>
            </div>

        </div>
    </div>

    <!-- Bootstrap JS y Librerías -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
    <script src="../../assests/js/admin-animations.js"></script>
</body>
</html>