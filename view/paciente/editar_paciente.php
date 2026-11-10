
<?php
require_once __DIR__ . '/../../app/recepcionista_auth.php';
requerir_recepcionista('../recepcionista/lista_recepcionista.php');

// Esta vista recibe $paciente desde app/logica_paciente.php.
function escapar($valor) {
    return htmlspecialchars(
        (string) ($valor ?? ""),
        ENT_QUOTES,
        "UTF-8"
    );
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar paciente</title>
</head>
<body>

    <h1>Editar paciente</h1>

    <form action="../../app/logica_paciente.php" method="POST">

        <input type="hidden" name="accion" value="actualizar">
        <input type="hidden" name="id"
               value="<?= escapar($paciente["id"]) ?>">

        <label for="nombre">Nombre:</label>
        <input
            type="text"
            id="nombre"
            name="nombre"
            value="<?= escapar($paciente["nombre"]) ?>"
            required
        >
        <br><br>

        <label for="telefono">Teléfono:</label>
        <input
            type="text"
            id="telefono"
            name="telefono"
            value="<?= escapar($paciente["telefono"]) ?>"
        >
        <br><br>

        <label for="email">Correo electrónico:</label>
        <input
            type="email"
            id="email"
            name="email"
            value="<?= escapar($paciente["email"]) ?>"
        >
        <br><br>

        <label for="fecha_nac">Fecha de nacimiento:</label>
        <input
            type="date"
            id="fecha_nac"
            name="fecha_nac"
            value="<?= escapar($paciente["fecha_nac"]) ?>"
        >
        <br><br>

        <label for="alergias">Alergias:</label>
        <textarea
            id="alergias"
            name="alergias"
        ><?= escapar($paciente["alergias"]) ?></textarea>
        <br><br>

        <button type="submit">Guardar cambios</button>

        <a href="lista_pacientes.php">Cancelar</a>

    </form>

</body>
</html>
