<?php
session_start();

$mensaje = $_SESSION["mensaje"] ?? null;
$tipo_mensaje = $_SESSION["tipo_mensaje"] ?? "exito";

unset($_SESSION["mensaje"], $_SESSION["tipo_mensaje"]);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar servicio | SCO</title>
</head>
<body>

    <h1>Registrar servicio</h1>

    <?php if ($mensaje !== null): ?>
        <p style="color: <?= $tipo_mensaje === "error" ? "#b00020" : "#1b7f3b" ?>;">
            <strong><?= htmlspecialchars($mensaje, ENT_QUOTES, "UTF-8") ?></strong>
        </p>
    <?php endif; ?>

    <form action="../../app/logica_servicio.php" method="POST">

        <input type="hidden" name="accion" value="registrar">

        <label for="nombre">Nombre del servicio:</label>
        <input
            type="text"
            id="nombre"
            name="nombre"
            maxlength="80"
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
            value="30"
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
            value="0"
            required
        >

        <br><br>

        <button type="submit">Registrar servicio</button>

    </form>

    <p>
        <a href="lista_servicios.php">Ver servicios registrados</a>
    </p>

    <p>
        <a href="../../index.php">Volver al inicio</a>
    </p>

</body>
</html>
