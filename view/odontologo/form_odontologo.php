
<?php
session_start();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar odontólogo | DENTIX</title>
</head>
<body>

    <h1>Registrar odontólogo</h1>

    <?php if (isset($_SESSION["mensaje"])): ?>
        <p>
            <?= htmlspecialchars($_SESSION["mensaje"], ENT_QUOTES, "UTF-8") ?>
        </p>

        <?php
        unset($_SESSION["mensaje"], $_SESSION["tipo_mensaje"]);
        ?>
    <?php endif; ?>

    <form action="../../app/logica_odontologo.php" method="POST">

        <input type="hidden" name="accion" value="registrar">

        <label for="nombre">Nombre completo:</label>
        <input
            type="text"
            id="nombre"
            name="nombre"
            maxlength="100"
            required
        >

        <br><br>

        <label for="especialidad">Especialidad:</label>
        <input
            type="text"
            id="especialidad"
            name="especialidad"
            maxlength="60"
            required
        >

        <br><br>

        <label for="consultorio">Consultorio:</label>
        <input
            type="text"
            id="consultorio"
            name="consultorio"
            maxlength="30"
        >

        <br><br>

        <button type="submit">Registrar odontólogo</button>

    </form>

    <p>
        <a href="lista_odontologos.php">Ver odontólogos registrados</a>
    </p>

    <p>
        <a href="../../index.php">Volver al inicio</a>
    </p>

</body>
</html>
