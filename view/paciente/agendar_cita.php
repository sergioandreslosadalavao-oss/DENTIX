<?php
// agendar_cita.php - Panel para que el paciente agende su cita directamente
require_once "../../config/database.php";

$mensaje = "";
$error = "";

// Obtener la lista de odontólogos activos para mostrarlos en el select
try {
    $stmt_odonto = $conexion->prepare("SELECT id, nombre FROM odontologo WHERE estado = 'activo' OR activo = true");
    $stmt_odonto->execute();
    $odontologos = $stmt_odonto->fetchAll();
} catch (PDOException $e) {
    $odontologos = [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cedula_paciente = trim($_POST['cedula'] ?? '');
    $id_odontologo = trim($_POST['id_odontologo'] ?? '');
    $fecha = trim($_POST['fecha'] ?? '');
    $hora = trim($_POST['hora'] ?? '');

    if (!empty($cedula_paciente) && !empty($id_odontologo) && !empty($fecha) && !empty($hora)) {
        try {
            // Verificar si el paciente existe mediante su cédula
            $stmt_paciente = $conexion->prepare("SELECT id FROM paciente WHERE cedula = ?");
            $stmt_paciente->execute([$cedula_paciente]);
            $paciente = $stmt_paciente->fetch();

            if ($paciente) {
                $paciente_id = $paciente['id'];

                // Guardar la cita asignando el paciente y el odontólogo seleccionado
                $stmt_cita = $conexion->prepare("INSERT INTO cita (paciente_id, odontologo_id, fecha, hora) VALUES (?, ?, ?, ?)");
                $stmt_cita->execute([$paciente_id, $id_odontologo, $fecha, $hora]);

                $mensaje = "¡Cita agendada con éxito! Te esperamos en la clínica.";
            } else {
                $error = "No encontramos ningún paciente registrado con esta cédula. Por favor regístrate primero.";
            }
        } catch (PDOException $e) {
            $error = "Error al agendar la cita: " . $e->getMessage();
        }
    } else {
        $error = "Por favor completa todos los campos para agendar la cita.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agendar Cita - DENTIX</title>
    
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
                    <span class="eyebrow mb-2">Portal Pacientes</span>
                    <h2 class="fw-bold text-dark mb-0 fs-3">
                        <i class="fa-solid fa-calendar-check text-primary me-2"></i> Agendar Cita
                    </h2>
                </div>
                <a href="../../index.php" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                    <i class="fa-solid fa-arrow-left me-1"></i> Inicio
                </a>
            </div>

            <?php if (!empty($mensaje)): ?>
                <div class="alert alert-success py-2 mb-4 small d-flex align-items-center" role="alert">
                    <i class="fa-solid fa-circle-check me-2 fs-5"></i> 
                    <div><?php echo $mensaje; ?></div>
                </div>
            <?php endif; ?>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger py-2 mb-4 small d-flex align-items-center" role="alert">
                    <i class="fa-solid fa-triangle-exclamation me-2 fs-5"></i> 
                    <div>
                        <?php echo $error; ?>
                        <div class="mt-1"><a href="form_paciente.php" class="fw-bold text-danger text-decoration-underline">Registrarme ahora</a></div>
                    </div>
                </div>
            <?php endif; ?>

            <form action="agendar_cita.php" method="POST">
                <div class="mb-3">
                    <label for="cedula" class="form-label fw-semibold small">Número de Cédula (Paciente Registrado)</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-id-card text-muted"></i></span>
                        <input type="text" class="form-control border-start-0" id="cedula" name="cedula" required placeholder="Ingresa tu cédula registrada">
                    </div>
                </div>

                <div class="mb-3">
                    <label for="id_odontologo" class="form-label fw-semibold small">Seleccionar Odontólogo</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-user-doctor text-muted"></i></span>
                        <select class="form-select border-start-0" id="id_odontologo" name="id_odontologo" required>
                            <option value="">-- Seleccione un especialista --</option>
                            <?php foreach ($odontologos as $odonto): ?>
                                <option value="<?php echo $odonto['id']; ?>"><?php echo htmlspecialchars($odonto['nombre']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="fecha" class="form-label fw-semibold small">Fecha de la Cita</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-calendar text-muted"></i></span>
                        <input type="date" class="form-control border-start-0" id="fecha" name="fecha" required>
                    </div>
                </div>

                <div class="mb-4">
                    <label for="hora" class="form-label fw-semibold small">Hora de la Cita</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-clock text-muted"></i></span>
                        <input type="time" class="form-control border-start-0" id="hora" name="hora" required>
                    </div>
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn--primary py-2 fw-bold">
                        <i class="fa-solid fa-calendar-plus me-2"></i> Confirmar y Agendar Cita
                    </button>
                </div>
            </form>

            <div class="text-center mt-4 pt-3 border-top">
                <span class="text-muted small">¿Aún no tienes cuenta?</span>
                <a href="form_paciente.php" class="text-primary small text-decoration-none fw-bold ms-1">Regístrate aquí</a>
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
    <script src="../../assests/js/admin-animations.js"></script>
</body>
</html>