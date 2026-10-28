<?php
// form_paciente.php - Registro de Nuevo Paciente
require_once "../../config/database.php";

$mensaje = "";
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $cedula = trim($_POST['cedula'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $correo = trim($_POST['correo'] ?? '');

    if (!empty($nombre) && !empty($cedula) && !empty($telefono) && !empty($correo)) {
        try {
            // Verificar si la cédula ya existe
            $check = $conexion->prepare("SELECT id FROM paciente WHERE cedula = ?");
            $check->execute([$cedula]);
            
            if ($check->rowCount() > 0) {
                $error = "Ya existe un paciente registrado con el número de cédula ingresado.";
            } else {
                $stmt = $conexion->prepare("INSERT INTO paciente (nombre, cedula, telefono, email) VALUES (?, ?, ?, ?)");
                $stmt->execute([$nombre, $cedula, $telefono, $correo]);

                $mensaje = "¡Paciente registrado con éxito en el sistema!";
            }
        } catch (PDOException $e) {
            $error = "Error al guardar el paciente: " . $e->getMessage();
        }
    } else {
        $error = "Por favor, completa todos los campos obligatorios.";
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
</head>
<body class="admin-body" style="display: grid; place-items: center; min-height: 100vh; padding: 24px 0;">

    <div class="container" style="max-width: 520px;">
        <div class="form-card shadow-lg p-4 p-md-5 border-0 rounded-4">
            
            <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
                <div>
                    <span class="eyebrow mb-2">Expedientes</span>
                    <h2 class="fw-bold text-dark mb-0 fs-3">
                        <i class="fa-solid fa-user-plus text-primary me-2"></i> Registrar Paciente
                    </h2>
                </div>
                <a href="../../index.php" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                    <i class="fa-solid fa-arrow-left me-1"></i> Volver
                </a>
            </div>

            <?php if (!empty($mensaje)): ?>
                <div class="alert alert-success py-2 mb-4 small d-flex align-items-center" role="alert">
                    <i class="fa-solid fa-circle-check me-2 fs-5"></i> 
                    <div>
                        <?php echo $mensaje; ?>
                        <div class="mt-1"><a href="lista_pacientes.php" class="fw-bold text-success text-decoration-underline">Ver listado de pacientes</a></div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger py-2 mb-4 small d-flex align-items-center" role="alert">
                    <i class="fa-solid fa-triangle-exclamation me-2 fs-5"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form action="form_paciente.php" method="POST">
                <div class="mb-3">
                    <label for="nombre" class="form-label fw-semibold small">Nombres y Apellidos</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-user text-muted"></i></span>
                        <input type="text" class="form-control border-start-0" id="nombre" name="nombre" required placeholder="Ej. Juan Pérez">
                    </div>
                </div>

                <div class="mb-3">
                    <label for="cedula" class="form-label fw-semibold small">Número de Cédula</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-id-card text-muted"></i></span>
                        <input type="text" class="form-control border-start-0" id="cedula" name="cedula" required placeholder="Ej. 123456789">
                    </div>
                </div>

                <div class="mb-3">
                    <label for="telefono" class="form-label fw-semibold small">Teléfono / Celular</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-phone text-muted"></i></span>
                        <input type="text" class="form-control border-start-0" id="telefono" name="telefono" required placeholder="Ej. 3001234567">
                    </div>
                </div>

                <div class="mb-4">
                    <label for="correo" class="form-label fw-semibold small">Correo Electrónico</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-envelope text-muted"></i></span>
                        <input type="email" class="form-control border-start-0" id="correo" name="correo" required placeholder="correo@ejemplo.com">
                    </div>
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn--primary py-2 fw-bold">
                        <i class="fa-solid fa-floppy-disk me-2"></i> Guardar Paciente
                    </button>
                </div>
            </form>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
    <script src="../../assests/js/admin-animations.js"></script>
</body>
</html>