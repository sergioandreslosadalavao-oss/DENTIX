<?php
require_once(__DIR__ . '/../../app/logica_recepcionista.php');
$logicaRec = new LogicaRecepcionista();
$odontologosActivos = $logicaRec->obtenerOdontologosActivos();

$id_cita = $_GET['id'] ?? null;
// Aquí puedes agregar la consulta para traer los datos actuales de la cita con el $id_cita si lo deseas
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Cita - Recepción</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="card shadow">
            <div class="card-header bg-warning text-dark">
                <h4>Panel de Recepción: Modificar Cita</h4>
            </div>
            <div class="card-body">
                <form action="../../app/actualizar_cita_recepcion.php" method="POST">
                    <input type="hidden" name="id_cita" value="<?php echo htmlspecialchars($id_cita); ?>">

                    <div class="mb-3">
                        <label for="id_odontologo" class="form-label">Reasignar Odontólogo Activo:</label>
                        <select class="form-select" name="id_odontologo" required>
                            <?php foreach ($odontologosActivos as $odonto): ?>
                                <option value="<?php echo $odonto['id_odontologo']; ?>">
                                    Dr(a). <?php echo htmlspecialchars($odonto['nombre'] . ' ' . $odonto['apellido'] . ' (' . $odonto['especialidad'] . ')'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="fecha_cita" class="form-label">Nueva Fecha:</label>
                        <input type="date" class="form-control" name="fecha_cita" required>
                    </div>

                    <div class="mb-3">
                        <label for="hora_cita" class="form-label">Nueva Hora:</label>
                        <input type="time" class="form-control" name="hora_cita" required>
                    </div>

                    <div class="mb-3">
                        <label for="estado" class="form-label">Estado de la Cita:</label>
                        <select class="form-select" name="estado">
                            <option value="Pendiente">Pendiente</option>
                            <option value="Confirmada">Confirmada</option>
                            <option value="Cancelada">Cancelada</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary">Actualizar Cambios</button>
                    <a href="lista_citas_recepcion.php" class="btn btn-secondary">Cancelar</a>
                </form>
            </div>
        </div>
    </div>
</body>
</html>