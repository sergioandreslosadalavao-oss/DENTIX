<?php
require_once "../../config/database.php";

// Obtener pacientes y odontólogos para los campos de selección
try {
    $pacientes = $conexion->query("SELECT id, nombre FROM paciente ORDER BY nombre ASC")->fetchAll();
    $odontologos = $conexion->query("SELECT id, nombre FROM odontologo ORDER BY nombre ASC")->fetchAll();
} catch (PDOException $e) {
    $pacientes = [];
    $odontologos = [];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Agendar Cita - SCO</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body class="bg-light">

    <div class="container my-5" style="max-width: 650px;">
        <div class="card border-0 shadow-lg rounded-4 p-4">
            <div class="card-body">
                <div class="text-center mb-4">
                    <h3 class="fw-bold text-primary"><i class="fa-solid fa-calendar-plus me-2"></i>Agendar Nueva Cita</h3>
                    <p class="text-muted">Diligencie los datos necesarios para programar la atención</p>
                </div>

                <form action="../../app/logica_cita.php" method="POST">
                    <!-- Selección de Paciente -->
                    <div class="mb-3">
                        <label for="paciente_id" class="form-label fw-bold"><i class="fa-solid fa-user me-1"></i> Paciente</label>
                        <select name="paciente_id" id="paciente_id" class="form-select" required>
                            <option value="" disabled selected>Seleccione un paciente...</option>
                            <?php foreach ($pacientes as $p): ?>
                                <option value="<?php echo $p['id']; ?>"><?php echo htmlspecialchars($p['nombre']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Selección de Odontólogo -->
                    <div class="mb-3">
                        <label for="odontologo_id" class="form-label fw-bold"><i class="fa-solid fa-user-doctor me-1"></i> Odontólogo Asignado</label>
                        <select name="odontologo_id" id="odontologo_id" class="form-select" required>
                            <option value="" disabled selected>Seleccione un especialista...</option>
                            <?php foreach ($odontologos as $o): ?>
                                <option value="<?php echo $o['id']; ?>"><?php echo htmlspecialchars($o['nombre']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Fecha y Hora -->
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="fecha" class="form-label fw-bold"><i class="fa-solid fa-calendar me-1"></i> Fecha</label>
                            <input type="date" name="fecha" id="fecha" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label for="hora" class="form-label fw-bold"><i class="fa-solid fa-clock me-1"></i> Hora</label>
                            <input type="time" name="hora" id="hora" class="form-control" required>
                        </div>
                    </div>

                    <!-- Botones -->
                    <div class="d-grid gap-2 mt-4">
                        <button type="submit" class="btn btn-success btn-lg"><i class="fa-solid fa-check-circle me-2"></i>Confirmar Agendamiento</button>
                        <a href="lista_pacientes.php" class="btn btn-outline-secondary"><i class="fa-solid fa-arrow-left me-2"></i>Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

</body>
</html>