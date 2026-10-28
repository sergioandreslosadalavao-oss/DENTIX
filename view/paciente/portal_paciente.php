<?php
// portal_paciente.php - Portal exclusivo para que el paciente valide su cédula y agende su cita
require_once "../../config/database.php";

$mensaje = "";
$error = "";
$paciente_encontrado = false;
$paciente_datos = null;

// Paso 1: Validar si el paciente ingresa su cédula para buscarlo
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verificar_cedula'])) {
    $cedula = trim($_POST['cedula'] ?? '');

    if (!empty($cedula)) {
        try {
            $stmt = $conexion->prepare("SELECT * FROM paciente WHERE cedula = ?");
            $stmt->execute([$cedula]);
            $paciente_datos = $stmt->fetch();

            if ($paciente_datos) {
                $paciente_encontrado = true;
            } else {
                $error = "No encontramos ningún registro con la cédula #{$cedula}. Por favor regístrese primero.";
            }
        } catch (PDOException $e) {
            $error = "Error al consultar la base de datos.";
        }
    } else {
        $error = "Por favor ingrese un número de cédula válido.";
    }
}

// Paso 2: Guardar la cita cuando el paciente ya está validado y llena los datos de la cita
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar_cita'])) {
    $paciente_id = trim($_POST['paciente_id'] ?? '');
    $id_odontologo = trim($_POST['id_odontologo'] ?? '');
    $fecha = trim($_POST['fecha'] ?? '');
    $hora = trim($_POST['hora'] ?? '');

    if (!empty($paciente_id) && !empty($id_odontologo) && !empty($fecha) && !empty($hora)) {
        try {
            // Guardar la cita para que luego la administre el recepcionista
            $stmt_cita = $conexion->prepare("INSERT INTO cita (paciente_id, odontologo_id, fecha, hora) VALUES (?, ?, ?, ?)");
            $stmt_cita->execute([$paciente_id, $id_odontologo, $fecha, $hora]);

            $mensaje = "¡Su cita ha sido agendada con éxito! La recepción la validará próximamente.";
            // Limpiar datos para reiniciar flujo o mostrar éxito
            $paciente_encontrado = false;
        } catch (PDOException $e) {
            $error = "Error al guardar la cita: " . $e->getMessage();
            $paciente_encontrado = true; // Mantener formulario abierto
        }
    } else {
        $error = "Complete todos los campos de la cita.";
        $paciente_encontrado = true;
    }
}

// Obtener lista de odontólogos activos para el select
try {
    $stmt_odonto = $conexion->prepare("SELECT id, nombre FROM odontologo WHERE estado = 'activo' OR activo = true");
    $stmt_odonto->execute();
    $odontologos = $stmt_odonto->fetchAll();
} catch (PDOException $e) {
    $odontologos = [];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal de Citas - Pacientes DENTIX</title>
    
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
                        <i class="fa-solid fa-calendar-days text-primary me-2"></i> Agendar Cita
                    </h2>
                </div>
                <a href="../../index.php" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                    <i class="fa-solid fa-arrow-left me-1"></i> Inicio
                </a>
            </div>

            <!-- Alertas de éxito o error general -->
            <?php if (!empty($mensaje)): ?>
                <div class="alert alert-success py-3 mb-4 small d-flex align-items-center shadow-sm" role="alert">
                    <i class="fa-solid fa-circle-check me-3 fs-4 text-success"></i> 
                    <div><strong>¡Excelente!</strong><br><?php echo $mensaje; ?></div>
                </div>
            <?php endif; ?>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger py-2 mb-4 small d-flex align-items-center" role="alert">
                    <i class="fa-solid fa-triangle-exclamation me-2 fs-5"></i> 
                    <div>
                        <?php echo $error; ?>
                        <div class="mt-1"><a href="form_paciente.php" class="fw-bold text-danger text-decoration-underline">Ir al formulario de registro</a></div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- CONDICIONAL: Si NO está validado, pedir cédula -->
            <?php if (!$paciente_encontrado): ?>
                <div class="text-center mb-4">
                    <p class="text-muted small">Para comenzar, por favor ingrese su número de cédula y verifique si ya se encuentra registrado en nuestra clínica.</p>
                </div>

                <form action="portal_paciente.php" method="POST">
                    <input type="hidden" name="verificar_cedula" value="1">
                    
                    <div class="mb-4">
                        <label for="cedula" class="form-label fw-semibold small">Número de Cédula</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="fa-solid id-card text-muted"></i><i class="fa-solid fa-id-card text-muted"></i></span>
                            <input type="text" class="form-control border-start-0" id="cedula" name="cedula" required placeholder="Ej. 123456789">
                        </div>
                    </div>

                    <div class="d-grid gap-2 mb-3">
                        <button type="submit" class="btn btn--primary py-2 fw-bold">
                            <i class="fa-solid fa-magnifying-glass me-2"></i> Continuar y Agendar
                        </button>
                    </div>

                    <div class="text-center mt-3">
                        <span class="text-muted small">¿No estás registrado?</span>
                        <a href="form_paciente.php" class="text-primary small fw-bold text-decoration-none ms-1">Regístrate aquí</a>
                    </div>
                </form>

            <!-- CONDICIONAL: Si SÍ está validado, mostrar formulario exclusivo de cita -->
            <?php else: ?>
                <div class="alert alert-info py-2 mb-4 small">
                    <i class="fa-solid fa-user-check me-1"></i> Paciente verificado: <strong><?php echo htmlspecialchars($paciente_datos['nombre']); ?></strong>
                </div>

                <form action="portal_paciente.php" method="POST">
                    <input type="hidden" name="guardar_cita" value="1">
                    <input type="hidden" name="paciente_id" value="<?php echo $paciente_datos['id']; ?>">

                    <div class="mb-3">
                        <label for="id_odontologo" class="form-label fw-semibold small">Seleccionar Odontólogo / Especialista</label>
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
                            <i class="fa-solid fa-calendar-check me-2"></i> Confirmar Cita
                        </button>
                    </div>
                </form>
            <?php endif; ?>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
    <script src="../../assests/js/admin-animations.js"></script>
</body>
</html>