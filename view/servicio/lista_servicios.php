<?php
require_once "../../config/database.php";

try {
    $stmt = $conexion->query("SELECT * FROM servicio ORDER BY id ASC");
    $servicios = $stmt->fetchAll();
} catch (PDOException $e) {
    $servicios = [];
    $error = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Servicios - SCO</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body class="bg-light">

    <div class="container my-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold text-primary mb-1"><i class="fa-solid fa-notes-medical me-2"></i>Servicios y Tratamientos</h2>
                <p class="text-muted mb-0">Catálogo de servicios disponibles</p>
            </div>
            <div>
                <a href="form_servicio.php" class="btn btn-success me-2"><i class="fa-solid fa-plus me-2"></i>Nuevo Servicio</a>
                <a href="../../index.php" class="btn btn-outline-secondary"><i class="fa-solid fa-house me-2"></i>Inicio</a>
            </div>
        </div>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger shadow-sm"><i class="fa-solid fa-triangle-exclamation me-2"></i>Error: <?php echo $error; ?></div>
        <?php endif; ?>

        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th class="py-3 px-4">ID</th>
                                <th class="py-3">Nombre del Servicio</th>
                                <th class="py-3">Descripción</th>
                                <th class="py-3">Costo / Precio</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($servicios)): ?>
                                <?php foreach ($servicios as $s): ?>
                                    <tr>
                                        <td class="px-4 fw-bold text-secondary">#<?php echo htmlspecialchars($s['id']); ?></td>
                                        <td class="fw-bold"><?php echo htmlspecialchars($s['nombre']); ?></td>
                                        <td class="text-muted"><?php echo htmlspecialchars($s['descripcion'] ?? 'Sin descripción'); ?></td>
                                        <td class="fw-bold text-success">$<?php echo number_format($s['precio'] ?? 0, 0, ',', '.'); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">
                                        <i class="fa-solid fa-receipt fa-2x d-block mb-2"></i>No hay servicios creados aún.
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