
<?php
// Esta vista recibe $odontologo desde logica_odontologo.php.
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar odontólogo | SCO</title>
</head>
<body>

    <h1>Editar odontólogo</h1>

    <form action="logica_odontologo.php" method="POST">

        <input type="hidden" name="accion" value="actualizar">

        <input
            type="hidden"
            name="id"
            value="<?= (int) $odontologo["id"] ?>"
        >

        <label for="nombre">Nombre completo:</label>
        <input
            type="text"
            id="nombre"
            name="nombre"
            maxlength="100"
            value="<?= htmlspecialchars($odontologo["nombre"], ENT_QUOTES, "UTF-8") ?>"
            required
        >

        <br><br>

        <label for="especialidad">Especialidad:</label>
        <input
            type="text"
            id="especialidad"
            name="especialidad"
            maxlength="60"
            value="<?= htmlspecialchars($odontologo["especialidad"], ENT_QUOTES, "UTF-8") ?>"
            required
        >

        <br><br>

        <label for="consultorio">Consultorio:</label>
        <input
            type="text"
            id="consultorio"
            name="consultorio"
            maxlength="30"
            value="<?= htmlspecialchars($odontologo["consultorio"] ?? "", ENT_QUOTES, "UTF-8") ?>"
        >

        <br><br>

        <p>
            <strong>Slug público actual:</strong>
            <?= htmlspecialchars($odontologo["slug_publico"], ENT_QUOTES, "UTF-8") ?>
        </p>

        <p>El slug se actualizará automáticamente si cambias el nombre.</p>

        <button type="submit">Guardar cambios</button>

    </form>

    <p>
        <a href="../view/odontologo/lista_odontologos.php">Cancelar y volver al listado</a>
    </p>
    <script src="assests/js/animaciones.js"></script>
</body>
</html>
