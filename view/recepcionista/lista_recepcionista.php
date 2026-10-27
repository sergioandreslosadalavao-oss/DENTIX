<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Incluir la conexión correctamente desde view/recepcionista/ hacia config/database.php
require_once __DIR__ . '/../../config/database.php';

// Obtener los datos de los recepcionistas usando la conexión global PDO
try {
    $stmt = $conexion->prepare("SELECT * FROM recepcionista");
    $stmt->execute();
    $recepcionistas = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $recepcionistas = [];
    $error_db = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Lista de Recepcionistas</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-4">
    <h2>Lista de Recepcionistas</h2>
    <div class="mb-3">
        <a href="form_recepcionista.php" class="btn btn-primary">Registrar Nueva Recepcionista</a>
        <a href="../../index.php" class="btn btn-secondary">Volver al Inicio</a>
    </div>
    
    <?php if (isset($error_db)): ?>
        <div class="alert alert-danger">Error en la base de datos: <?php echo $error_db; ?></div>
    <?php endif; ?>

    <table class="table table-striped table-bordered">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Apellido</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($recepcionistas)): ?>
                <?php foreach ($recepcionistas as $row): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['id_recepcionista'] ?? $row['id'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($row['nombre'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($row['apellido'] ?? ''); ?></td>
                        <td>
                            <a href="editar_recepcionista.php?id=<?php echo $row['id_recepcionista'] ?? $row['id'] ?? ''; ?>" class="btn btn-sm btn-warning">Editar</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4" class="text-center">No hay recepcionistas registrados.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>