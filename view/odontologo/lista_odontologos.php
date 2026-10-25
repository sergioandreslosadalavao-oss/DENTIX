
<?php

session_start();

require_once __DIR__ . "/../../config/database.php";

try {
    $sql = "SELECT id, nombre, especialidad, consultorio, slug_publico
            FROM odontologo
            ORDER BY id ASC";

    $consulta = $conexion->query($sql);
    $odontologos = $consulta->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log("Error al listar odontólogos: " . $e->getMessage());

    $odontologos = [];
    $_SESSION["mensaje"] = "No se pudo cargar el listado de odontólogos.";
    $_SESSION["tipo_mensaje"] = "error";
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Odontólogos | SCO</title>
</head>
<body>

    <h1>Odontólogos registrados</h1>

    <?php if (isset($_SESSION["mensaje"])): ?>
        <p>
            <?= htmlspecialchars($_SESSION["mensaje"], ENT_QUOTES, "UTF-8") ?>
        </p>

        <?php
        unset($_SESSION["mensaje"], $_SESSION["tipo_mensaje"]);
        ?>
    <?php endif; ?>

    <p>
        <a href="form_odontologo.php">Registrar odontólogo</a>
    </p>

    <?php if (count($odontologos) > 0): ?>

        <table border="1" cellpadding="8">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Especialidad</th>
                    <th>Consultorio</th>
                    <th>Slug público</th>
                    <th>Acciones</th>
                </tr>
            </thead>

            <tbody>
                <?php foreach ($odontologos as $odontologo): ?>
                    <tr>
                        <td>
                            <?= (int) $odontologo["id"] ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($odontologo["nombre"], ENT_QUOTES, "UTF-8") ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($odontologo["especialidad"], ENT_QUOTES, "UTF-8") ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($odontologo["consultorio"] ?? "", ENT_QUOTES, "UTF-8") ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($odontologo["slug_publico"], ENT_QUOTES, "UTF-8") ?>
                        </td>

                        <td>
                            <a href="../../app/logica_odontologo.php?accion=editar&id=<?= (int) $odontologo["id"] ?>">
                                Editar
                            </a>

                            <form
                                action="../../app/logica_odontologo.php"
                                method="POST"
                                style="display:inline"
                                onsubmit="return confirm('¿Seguro que deseas eliminar este odontólogo?');"
                            >
                                <input type="hidden" name="accion" value="eliminar">

                                <input
                                    type="hidden"
                                    name="id"
                                    value="<?= (int) $odontologo["id"] ?>"
                                >

                                <button type="submit">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

    <?php else: ?>

        <p>No hay odontólogos registrados.</p>

    <?php endif; ?>

    <p>
        <a href="../../index.php">Volver al inicio</a>
    </p>

</body>
</html>
