<?php
require_once "../../config/database.php";

try {
    $stmt = $conexion->query("SELECT * FROM odontologo ORDER BY id ASC");
    $odontologos = $stmt->fetchAll();
} catch (PDOException $e) {
    $odontologos = [];
    $error = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Odontólogos - SCO</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body class="bg-light">

    <div class="container my-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold text-primary mb-1"><i class="fa-solid fa-user-doctor me-2"></i>Planta de Odontólogos</h2>
                <p class="text-muted mb-0">Especialistas registrados en la clínica</p>
            </div>
            <div>
                <a href="form_odontologo.php" class="btn btn-success me-2"><i class="fa-solid fa-user-plus me-2"></i>Nuevo Odontólogo</a>
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
                                <th class="py-3">Nombre Especialista</th>
                                <th class="py-3">Especialidad</th>
                                <th class="py-3">Teléfono</th>
                                <th class="py-3">Correo</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($odontologos)): ?>
                                <?php foreach ($odontologos as $o): ?>
                                    <tr>
                                        <td class="px-4 fw-bold text-secondary">#<?php echo htmlspecialchars($o['id']); ?></td>
                                        <td class="fw-bold"><?php echo htmlspecialchars($o['nombre']); ?></td>
                                        <td><span class="badge bg-primary-subtle text-primary border border-primary-subtle"><?php echo htmlspecialchars($o['especialidad'] ?? 'General'); ?></span></td>
                                        <td><i class="fa-solid fa-phone text-muted me-1"></i> <?php echo htmlspecialchars($o['telefono'] ?? 'N/A'); ?></td>
                                        <td><i class="fa-solid fa-envelope text-muted me-1"></i> <?php echo htmlspecialchars($o['email'] ?? 'N/A'); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">
                                        <i class="fa-solid fa-user-slash fa-2x d-block mb-2"></i>No hay odontólogos registrados.
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