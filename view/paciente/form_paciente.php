<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registrar paciente</title>
</head>
<body>

    <h1>Registrar paciente</h1>

    <form action="../../app/logica_paciente.php" method="POST">

        <label for="nombre">Nombre:</label>
        <input type="text" id="nombre" name="nombre" required>

        <br><br>

        <label for="telefono">Teléfono:</label>
        <input type="text" id="telefono" name="telefono" required>

        <br><br>

        <label for="email">Correo:</label>
        <input type="email" id="email" name="email" required>

        <br><br>

        <label for="fecha_nac">Fecha de nacimiento:</label>
        <input type="date" id="fecha_nac" name="fecha_nac">

        <br><br>

        <label for="alergias">Alergias:</label>
        <textarea id="alergias" name="alergias"></textarea>

        <br><br>

        <button type="submit">Registrar paciente</button>

    </form>

</body>
</html>