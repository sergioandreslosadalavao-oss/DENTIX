<?php

require_once __DIR__ . "/../../app/recepcionista_auth.php";
requerir_recepcionista("../recepcionista/lista_recepcionista.php");

require_once __DIR__ . "/../../config/database.php";

$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if ($id === false || $id === null || $id <= 0) {
    $_SESSION["mensaje"] = "Error: el ID del servicio no es válido.";
    $_SESSION["tipo_mensaje"] = "error";
    header("Location: lista_servicios.php");
    exit;
}

try {
    $consulta = $conexion->prepare(
        "SELECT id, nombre, descripcion, duracion_min, precio FROM servicio WHERE id = :id"
    );
    $consulta->execute([":id" => $id]);
    $servicio = $consulta->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error al consultar servicio: " . $e->getMessage());
    $servicio = false;
}

if (!$servicio) {
    $_SESSION["mensaje"] = "No se encontró el servicio solicitado.";
    $_SESSION["tipo_mensaje"] = "error";
    header("Location: lista_servicios.php");
    exit;
}

$mensaje = $_SESSION["mensaje"] ?? null;
$tipo_mensaje = $_SESSION["tipo_mensaje"] ?? "exito";

unset($_SESSION["mensaje"], $_SESSION["tipo_mensaje"]);

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Servicio - DENTIX</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@500;600;700;800&family=Sora:wght@700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="../../assests/css/admin.css">
    <link rel="stylesheet" href="../../assests/css/index.css">
</head>
<body class="admin-body" style="display: grid; place-items: center; min-height: 100vh; padding: 30px 15px;">

    <div class="container" style="max-width: 580px;">
        <div class="form-card shadow-lg p-4 p-md-5 border-0 rounded-4 bg-white">
            
            <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
                <div>
                    <span class="eyebrow mb-1 text-primary fw-bold" style="font-size: 0.75rem; letter-spacing: 0.05em; text-transform: uppercase;">Administración de Tratamientos</span>
                    <h2 class="fw-bold text-dark mb-0 fs-3" style="font-family: 'Sora', sans-serif;">
                        <i class="fa-solid fa-pen-to-square text-primary me-2"></i> Editar servicio
                    </h2>
                </div>
                <a href="lista_servicios.php" class="btn btn-outline-secondary btn-sm rounded-pill px-3 fw-semibold">
                    <i class="fa-solid fa-arrow-left me-1"></i> Volver
                </a>
            </div>

            <?php if ($mensaje !== null): ?>
                <div class="alert <?= $tipo_mensaje === 'error' ? 'alert-danger' : 'alert-success' ?> py-2 mb-4 small d-flex align-items-center rounded-3" role="alert">
                    <i class="fa-solid <?= $tipo_mensaje === 'error' ? 'fa-triangle-exclamation' : 'fa-circle-check' ?> me-2 fs-5"></i>
                    <div><?= htmlspecialchars($mensaje, ENT_QUOTES, "UTF-8") ?></div>
                </div>
            <?php endif; ?>

            <form action="../../app/logica_servicio.php" method="POST">
                <input type="hidden" name="accion" value="actualizar">
                <input type="hidden" name="id" value="<?= (int) $servicio["id"] ?>">

                <div class="mb-3">
                    <label for="nombre" class="form-label fw-semibold small text-dark">Nombre del Servicio</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-tooth text-muted"></i></span>
                        <input
                            type="text"
                            class="form-control border-start-0"
                            id="nombre"
                            name="nombre"
                            maxlength="80"
                            value="<?= htmlspecialchars($servicio["nombre"], ENT_QUOTES, "UTF-8") ?>"
                            required
                            placeholder="Ej. Ortodoncia correctiva"
                        >
                    </div>
                </div>

                <div class="mb-3">
                    <label for="descripcion" class="form-label fw-semibold small text-dark">Descripción Clínica</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-align-left text-muted"></i></span>
                        <textarea
                            class="form-control border-start-0"
                            id="descripcion"
                            name="descripcion"
                            rows="3"
                            placeholder="Describe brevemente el procedimiento, beneficios o alcance..."
                        ><?= htmlspecialchars($servicio["descripcion"] ?? "", ENT_QUOTES, "UTF-8") ?></textarea>
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label for="duracion_min" class="form-label fw-semibold small text-dark">Duración estimada</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-clock text-muted"></i></span>
                            <input
                                type="number"
                                class="form-control border-start-0"
                                id="duracion_min"
                                name="duracion_min"
                                min="15"
                                step="15"
                                value="<?= (int) $servicio["duracion_min"] ?>"
                                required
                            >
                            <span class="input-group-text bg-light">min</span>
                        </div>
                        <small class="text-muted">Mínimo 15 min.</small>
                    </div>

                    <div class="col-md-6">
                        <label for="precio" class="form-label fw-semibold small text-dark">Tarifa / Precio</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-dollar-sign text-muted"></i></span>
                            <input
                                type="number"
                                class="form-control border-start-0"
                                id="precio"
                                name="precio"
                                min="0"
                                step="0.01"
                                value="<?= htmlspecialchars($servicio["precio"], ENT_QUOTES, "UTF-8") ?>"
                                required
                            >
                            <span class="input-group-text bg-light">COP</span>
                        </div>
                        <small class="text-muted">En pesos colombianos.</small>
                    </div>
                </div>

                <div class="d-grid gap-2 pt-2">
                    <button type="submit" class="btn btn--primary py-2 fw-bold rounded-3 shadow-sm">
                        <i class="fa-solid fa-floppy-disk me-2"></i> Guardar cambios
                    </button>
                    <a href="lista_servicios.php" class="btn btn-light py-2 text-muted fw-semibold rounded-3 text-center">
                        Cancelar y volver al catálogo
                    </a>
                </div>
            </form>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
