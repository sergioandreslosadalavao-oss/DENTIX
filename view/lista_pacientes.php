
<?php

session_start();

require_once __DIR__ . "/../config/database.php";

// Recuperar mensajes de la operación anterior.
$mensaje = $_SESSION["mensaje"] ?? null;
$tipo_mensaje = $_SESSION["tipo_mensaje"] ?? "exito";

unset($_SESSION["mensaje"], $_SESSION["tipo_mensaje"]);

// Obtener el término de búsqueda.
$busqueda = trim($_GET["nombre"] ?? "");

// Consultar todos los datos registrados del paciente.
if ($busqueda !== "") {

    $sql = "SELECT id, nombre, email, alergias
            FROM paciente
            WHERE nombre ILIKE :nombre
            ORDER BY id";

    $consulta = $conexion->prepare($sql);
    $consulta->execute([
        ":nombre" => "%" . $busqueda . "%"
    ]);

} else {

    $sql = "SELECT id, nombre, email, alergias
            FROM paciente
            ORDER BY id";

    $consulta = $conexion->query($sql);
}

$pacientes = $consulta->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Lista de pacientes</title>
</head>
<body>

    <h1>Pacientes registrados</h1>

    <?php if ($mensaje !== null): ?>
        <p>
            <strong>
                <?= htmlspecialchars($mensaje, ENT_QUOTES, "UTF-8") ?>
            </strong>
        </p>
    <?php endif; ?>

    <a href="form_paciente.php">Registrar paciente</a>

    <h2>Buscar pacientes por nombre</h2>

    <form action="lista_pacientes.php" method="GET">
        <label for="nombre">Nombre:</label>

        <input
            type="text"
            id="nombre"
            name="nombre"
            value="<?= htmlspecialchars($busqueda, ENT_QUOTES, "UTF-8") ?>"
            placeholder="Escribe un nombre"
        >

        <button type="submit">Buscar</button>
        <a href="lista_pacientes.php">Mostrar todos</a>
    </form>

    <h2>Listado de pacientes</h2>

    <?php if (count($pacientes) > 0): ?>

        <table border="1" cellpadding="8">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                   
                    <th>Correo electrónico</th>
                    
                    <th>Alergias</th>
                </tr>
            </thead>

            <tbody>
                <?php foreach ($pacientes as $paciente): ?>
                    <tr>
                        <td><?= (int) $paciente["id"] ?></td>

                        <td>
                            <?= htmlspecialchars(
                                $paciente["nombre"] ?? "",
                                ENT_QUOTES,
                                "UTF-8"
                            ) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars(
                                $paciente["email"] ?? "",
                                ENT_QUOTES,
                                "UTF-8"
                            ) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars(
                                $paciente["alergias"] ?? "",
                                ENT_QUOTES,
                                "UTF-8"
                            ) ?>
                        </td>

                        <td>
                            <a href="../app/logica_paciente.php?accion=editar&amp;id=<?= (int) $paciente["id"] ?>">
                                Editar
                            </a>

                            <br><br>

                            <form
                                action="../app/logica_paciente.php"
                                method="POST"
                                onsubmit="return confirm('¿Deseas eliminar este paciente?');"
                            >
                                <input
                                    type="hidden"
                                    name="accion"
                                    value="eliminar"
                                >

                                <input
                                    type="hidden"
                                    name="id"
                                    value="<?= (int) $paciente["id"] ?>"
                                >

                                <button type="submit">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

    <?php else: ?>

        <p>No se encontraron pacientes.</p>

    <?php endif; ?>

</body>
</html>
