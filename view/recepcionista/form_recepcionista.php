
<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once(__DIR__ . '/../../app/logica_recepcionista.php');
$logicaRec = new LogicaRecepcionista();
$odontologosActivos = $logicaRec->obtenerOdontologosActivos();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Asignar Cita - Recepción</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h4>Panel de Recepción: Asignar Nueva Cita</h4>
            </div>
            <div class="card-body">
                <form action="../../app/procesar_cita_recepcion.php" method="POST">
                    
                    <div class="mb-3">
                        <label for="id_paciente" class="form-label">ID o Documento del Paciente:</label>
                        <input type="text" class="form-control" name="id_paciente" required placeholder="Ingrese ID del paciente">
                    </div>

                    <div class="mb-3">
                        <label for="id_odontologo" class="form-label">Seleccionar Odontólogo (Solo Activos):</label>
                        <select class="form-select" name="id_odontologo" required>
                            <option value="">-- Seleccione un especialista ---</option>
                            <?php foreach ($odontologosActivos as $odonto): ?>
                                <option value="<?php echo $odonto['id_odontologo']; ?>">
                                    Dr(a). <?php echo htmlspecialchars($odonto['nombre'] . ' ' . $odonto['apellido'] . ' (' . $odonto['especialidad'] . ')'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="fecha_cita" class="form-label">Fecha de la Cita:</label>
                        <input type="date" class="form-control" name="fecha_cita" required>
                    </div>

                    <div class="mb-3">
                        <label for="hora_cita" class="form-label">Hora de la Cita:</label>
                        <input type="time" class="form-control" name="hora_cita" required>
                    </div>

                    <button type="submit" class="btn btn-success">Guardar y Asignar Cita</button>
                    <a href="../index.php" class="btn btn-secondary">Cancelar</a>
                </form>
            </div>
        </div>
    </div>
</body>
</html>