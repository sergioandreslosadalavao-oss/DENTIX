<?php

session_start();

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
        "SELECT id, nombre, duracion_min, precio FROM servicio WHERE id = :id"
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
    <title>Editar servicio | SCO</title>
</head>
<body>

    <h1>Editar servicio</h1>

    <?php if ($mensaje !== null): ?>
        <p style="color: <?= $tipo_mensaje === "error" ? "#b00020" : "#1b7f3b" ?>;">
            <strong><?= htmlspecialchars($mensaje, ENT_QUOTES, "UTF-8") ?></strong>
        </p>
    <?php endif; ?>

    <form action="../../app/logica_servicio.php" method="POST">

        <input type="hidden" name="accion" value="actualizar">
        <input type="hidden" name="id" value="<?= (int) $servicio["id"] ?>">

        <label for="nombre">Nombre del servicio:</label>
        <input
            type="text"
            id="nombre"
            name="nombre"
            maxlength="80"
            value="<?= htmlspecialchars($servicio["nombre"], ENT_QUOTES, "UTF-8") ?>"
            required
        >

        <br><br>

        <label for="duracion_min">Duración (minutos, mínimo 15):</label>
        <input
            type="number"
            id="duracion_min"
            name="duracion_min"
            min="15"
            step="15"
            value="<?= (int) $servicio["duracion_min"] ?>"
            required
        >

        <br><br>

        <label for="precio">Precio (COP):</label>
        <input
            type="number"
            id="precio"
            name="precio"
            min="0"
            step="0.01"
            value="<?= htmlspecialchars($servicio["precio"], ENT_QUOTES, "UTF-8") ?>"
            required
        >

        <br><br>

        <button type="submit">Guardar cambios</button>

    </form>

    <p>
        <a href="lista_servicios.php">Cancelar y volver al listado</a>
    </p>

</body>
</html>
