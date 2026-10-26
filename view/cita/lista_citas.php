<?php
require_once "../../config/database.php";

// Consultar las citas usando la vista o un JOIN con las tablas relacionadas
try {
    $query = "SELECT c.id, p.nombre AS paciente, o.nombre AS odontologo, s.nombre AS servicio, c.fecha, c.hora, c.motivo 
              FROM cita c
              INNER JOIN paciente p ON c.paciente_id = p.id
              INNER JOIN odontologo o ON c.odontologo_id = o.id
              INNER JOIN servicio s ON c.servicio_id = s.id
              ORDER BY c.fecha DESC, c.hora DESC";
    $citas = $conexion->query($query)->fetchAll();
} catch (PDOException $e) {
    $citas = [];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Lista de Citas - SCO</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body class="bg-light">

    <div class="container my-5">
        <div class="card border-0 shadow-lg rounded-4 p-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h3 class="fw-bold text-primary"><i class="fa-solid fa-calendar-days me-2"></i>Agenda y Listado de Citas</h3>
                        <p class="text-muted mb-0">Control general de citas odontológicas programadas</p>
                    </div>
                    <div>
                        <a href="form_cita.php" class="btn btn-success">
                            <i class="fa-solid fa-calendar-plus me-2"></i>Nueva Cita
                        </a>
                        <a href="../../index.php" class="btn btn-outline-secondary">
                            <i class="fa-solid fa-house me-2"></i>Menú Principal
                        </a>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <table class="table table-hover align-middle">
                            <thead class="table-dark">
                                <tr>
                                    <th>#</th>
                                    <th>Paciente</th>
                                    <th>Odontólogo</th>
                                    <th>Servicio</th>
                                    <th>Fecha</th>
                                    <th>Hora</th>
                                    <th>Motivo</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($citas) > 0): ?>
                                    <?php foreach ($citas as $index => $cita): ?>
                                        <tr>
                                            <td><?php echo $index + 1; ?></td>
                                            <td class="fw-bold"><?php echo htmlspecialchars($cita['paciente']); ?></td>
                                            <td><?php echo htmlspecialchars($cita['odontologo']); ?></td>
                                            <td><span class="badge bg-info text-dark"><?php echo htmlspecialchars($cita['servicio']); ?></span></td>
                                            <td><?php echo htmlspecialchars($cita['fecha']); ?></td>
                                            <td><?php echo htmlspecialchars($cita['hora']); ?></td>
                                            <td><?php echo htmlspecialchars($cita['motivo']); ?></td>
                                            
                                            <td class="text-center">
                                                <a href="editar_cita.php?id=<?php echo $cita['id']; ?>" class="btn btn-sm btn-warning" title="Editar">
                                                    <i class="fa-solid fa-pen-to-square"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="8" class="text-center text-muted py-4">No hay citas programadas actualmente.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>