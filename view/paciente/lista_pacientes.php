<?php
require_once "../../config/database.php";

try {
    $stmt = $conexion->query("SELECT * FROM paciente ORDER BY id ASC");
    $pacientes = $stmt->fetchAll();
} catch (PDOException $e) {
    $pacientes = [];
    $error = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Pacientes - SCO</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body class="bg-light">

    <div class="container my-5">
        <!-- Encabezado con Botones -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold text-primary mb-1"><i class="fa-solid fa-hospital-user me-2"></i>Gestión de Pacientes</h2>
                <p class="text-muted mb-0">Listado de pacientes registrados en el sistema</p>
            </div>
            <div>
                <a href="form_paciente.php" class="btn btn-success me-2"><i class="fa-solid fa-user-plus me-2"></i>Nuevo Paciente</a>
                <a href="../../index.php" class="btn btn-outline-secondary"><i class="fa-solid fa-house me-2"></i>Inicio</a>
            </div>
        </div>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger shadow-sm"><i class="fa-solid fa-triangle-exclamation me-2"></i>Error: <?php echo $error; ?></div>
        <?php endif; ?>

        <!-- Tabla de Datos -->
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th class="py-3 px-4">ID</th>
                                <th class="py-3">Nombre Completo</th>
                                <th class="py-3">Teléfono</th>
                                <th class="py-3">Correo Electrónico</th>
                                <th class="py-3">Fecha Nacimiento</th>
                                <th class="py-3 text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($pacientes)): ?>
                                <?php foreach ($pacientes as $p): ?>
                                    <tr>
                                        <td class="px-4 fw-bold text-secondary">#<?php echo htmlspecialchars($p['id']); ?></td>
                                        <td>
                                            <div class="fw-bold"><?php echo htmlspecialchars($p['nombre']); ?></div>
                                            <?php if (!empty($p['alergias'])): ?>
                                                <small class="badge bg-danger-subtle text-danger">Alergias: <?php echo htmlspecialchars($p['alergias']); ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td><i class="fa-solid fa-phone text-muted me-1"></i> <?php echo htmlspecialchars($p['telefono']); ?></td>
                                        <td><i class="fa-solid fa-envelope text-muted me-1"></i> <?php echo htmlspecialchars($p['email'] ?? 'N/A'); ?></td>
                                        <td><i class="fa-solid fa-calendar-day text-muted me-1"></i> <?php echo htmlspecialchars($p['fecha_nac'] ?? 'N/A'); ?></td>
                                        <td class="text-center">
                                            <a href="editar_paciente.php?id=<?php echo $p['id']; ?>" class="btn btn-sm btn-outline-warning"><i class="fa-solid fa-pen"></i></a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">
                                        <i class="fa-solid fa-inbox fa-2x d-block mb-2"></i>No hay pacientes registrados en el sistema.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</body>
</html>