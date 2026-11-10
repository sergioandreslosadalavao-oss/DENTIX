<?php
require_once __DIR__ . '/../../app/recepcionista_auth.php';
requerir_recepcionista('../recepcionista/lista_recepcionista.php');
require_once "../../config/database.php";

// Obtener el ID de la cita a editar
$id = $_GET['id'] ?? null;

if (!$id) {
    header("Location: lista_citas.php");
    exit();
}

try {
    // Consultar la cita actual
    $stmt = $conexion->prepare("SELECT * FROM cita WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $cita = $stmt->fetch();

    if (!$cita) {
        header("Location: lista_citas.php");
        exit();
    }

    // Obtener catálogos para los selects
    $pacientes = $conexion->query("SELECT id, nombre FROM paciente ORDER BY nombre ASC")->fetchAll();
    $odontologos = $conexion->query("SELECT id, nombre FROM odontologo WHERE activo = TRUE ORDER BY nombre ASC")->fetchAll();
    $servicios = $conexion->query("SELECT id, nombre FROM servicio ORDER BY nombre ASC")->fetchAll();

} catch (PDOException $e) {
    $error = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Cita - SCO</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome para Iconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body class="bg-light">

    <div class="container my-5" style="max-width: 650px;">
        <div class="card border-0 shadow-lg rounded-4 p-4">
            <div class="card-body">
                <div class="text-center mb-4">
                    <h3 class="fw-bold text-primary"><i class="fa-solid fa-calendar-pen me-2"></i>Editar Cita #<?php echo htmlspecialchars($cita['id']); ?></h3>
                    <p class="text-muted">Modifique los datos necesarios de la programación</p>
                </div>

                <?php if (isset($error)): ?>
                    <div class="alert alert-danger shadow-sm"><i class="fa-solid fa-triangle-exclamation me-2"></i>Error: <?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <form action="../../app/logica_cita.php" method="POST">
                    <!-- Campo oculto para indicar acción de actualización -->
                    <input type="hidden" name="accion" value="actualizar">
                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($cita['id']); ?>">

                    <!-- Selección de Paciente -->
                    <div class="mb-3">
                        <label for="paciente_id" class="form-label fw-bold"><i class="fa-solid fa-user me-1"></i> Paciente</label>
                        <select name="paciente_id" id="paciente_id" class="form-select" required>
                            <?php foreach ($pacientes as $p): ?>
                                <option value="<?php echo $p['id']; ?>" <?php echo ($p['id'] == $cita['paciente_id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($p['nombre']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Selección de Odontólogo (Solo Activos) -->
                    <div class="mb-3">
                        <label for="odontologo_id" class="form-label fw-bold"><i class="fa-solid fa-user-doctor me-1"></i> Odontólogo Asignado</label>
                        <select name="odontologo_id" id="odontologo_id" class="form-select" required>
                            <?php foreach ($odontologos as $o): ?>
                                <option value="<?php echo $o['id']; ?>" <?php echo ($o['id'] == $cita['odontologo_id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($o['nombre']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Selección de Servicio -->
                    <div class="mb-3">
                        <label for="servicio_id" class="form-label fw-bold"><i class="fa-solid fa-tooth me-1"></i> Servicio Odontológico</label>
                        <select name="servicio_id" id="servicio_id" class="form-select" required>
                            <?php foreach ($servicios as $s): ?>
                                <option value="<?php echo $s['id']; ?>" <?php echo ($s['id'] == $cita['servicio_id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($s['nombre']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Fecha y Hora -->
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="fecha" class="form-label fw-bold"><i class="fa-solid fa-calendar me-1"></i> Fecha</label>
                            <input type="date" name="fecha" id="fecha" class="form-control" value="<?php echo htmlspecialchars($cita['fecha']); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="hora" class="form-label fw-bold"><i class="fa-solid fa-clock me-1"></i> Hora</label>
                            <input type="time" name="hora" id="hora" class="form-control" value="<?php echo htmlspecialchars($cita['hora']); ?>" required>
                        </div>
                    </div>

                    <!-- Estado de la Cita -->
                    <div class="mb-3">
                        <label for="estado" class="form-label fw-bold"><i class="fa-solid fa-info-circle me-1"></i> Estado de la Cita</label>
                        <select name="estado" id="estado" class="form-select" required>
                            <option value="pendiente" <?php echo ($cita['estado'] === 'pendiente') ? 'selected' : ''; ?>>Pendiente</option>
                            <option value="confirmada" <?php echo ($cita['estado'] === 'confirmada') ? 'selected' : ''; ?>>Confirmada</option>
                            <option value="completada" <?php echo ($cita['estado'] === 'completada') ? 'selected' : ''; ?>>Completada</option>
                            <option value="cancelada" <?php echo ($cita['estado'] === 'cancelada') ? 'selected' : ''; ?>>Cancelada</option>
                            <option value="no_show" <?php echo ($cita['estado'] === 'no_show') ? 'selected' : ''; ?>>No Asistió (No Show)</option>
                        </select>
                    </div>

                    <!-- Motivo -->
                    <div class="mb-3">
                        <label for="motivo" class="form-label fw-bold"><i class="fa-solid fa-comment-medical me-1"></i> Motivo de Consulta</label>
                        <input type="text" name="motivo" id="motivo" class="form-control" value="<?php echo htmlspecialchars($cita['motivo'] ?? ''); ?>">
                    </div>

                    <!-- Botones de Acción -->
                    <div class="d-grid gap-2 mt-4">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fa-solid fa-save me-2"></i>Guardar Cambios
                        </button>
                        <a href="lista_citas.php" class="btn btn-outline-secondary">
                            <i class="fa-solid fa-arrow-left me-2"></i>Cancelar y volver
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>