<?php

session_start();

require_once __DIR__ . "/../../config/database.php";

// Mensajes de la operación anterior
$mensaje = $_SESSION["mensaje"] ?? null;
$tipo_mensaje = $_SESSION["tipo_mensaje"] ?? "exito";

unset($_SESSION["mensaje"], $_SESSION["tipo_mensaje"]);

// Búsqueda opcional por nombre
$busqueda = trim($_GET["nombre"] ?? "");

try {
    if ($busqueda !== "") {
        $consulta = $conexion->prepare(
            "SELECT id, nombre, duracion_min, precio
             FROM servicio
             WHERE nombre ILIKE :nombre
             ORDER BY id ASC"
        );
        $consulta->execute([":nombre" => "%" . $busqueda . "%"]);
    } else {
        $consulta = $conexion->query(
            "SELECT id, nombre, duracion_min, precio
             FROM servicio
             ORDER BY id ASC"
        );
    }

    $servicios = $consulta->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log("Error al listar servicios: " . $e->getMessage());

    $servicios = [];
    $mensaje = "No se pudo cargar el listado de servicios.";
    $tipo_mensaje = "error";
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Servicios | SCO</title>
</head>
<body>

    <h1>Servicios registrados</h1>

    <?php if ($mensaje !== null): ?>
        <p style="color: <?= $tipo_mensaje === "error" ? "#b00020" : "#1b7f3b" ?>;">
            <strong><?= htmlspecialchars($mensaje, ENT_QUOTES, "UTF-8") ?></strong>
        </p>
    <?php endif; ?>

    <p>
        <a href="form_servicio.php">Registrar servicio</a>
    </p>

    <form action="lista_servicios.php" method="GET">
        <label for="nombre">Buscar por nombre:</label>
        <input
            type="text"
            id="nombre"
            name="nombre"
            value="<?= htmlspecialchars($busqueda, ENT_QUOTES, "UTF-8") ?>"
        >
        <button type="submit">Buscar</button>
        <?php if ($busqueda !== ""): ?>
            <a href="lista_servicios.php">Limpiar</a>
        <?php endif; ?>
    </form>

    <br>

    <?php if (count($servicios) > 0): ?>

        <table border="1" cellpadding="8">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Duración (min)</th>
                    <th>Precio</th>
                    <th>Acciones</th>
                </tr>
            </thead>

            <tbody>
                <?php foreach ($servicios as $servicio): ?>
                    <tr>
                        <td><?= (int) $servicio["id"] ?></td>

                        <td>
                            <?= htmlspecialchars($servicio["nombre"], ENT_QUOTES, "UTF-8") ?>
                        </td>

                        <td><?= (int) $servicio["duracion_min"] ?> min</td>

                        <td>
                            $ <?= number_format((float) $servicio["precio"], 2, ",", ".") ?>
                        </td>

                        <td>
                            <a href="editar_servicio.php?id=<?= (int) $servicio["id"] ?>">
                                Editar
                            </a>

                            <form
                                action="../../app/logica_servicio.php"
                                method="POST"
                                style="display:inline"
                                onsubmit="return confirm('¿Seguro que deseas eliminar este servicio?');"
                            >
                                <input type="hidden" name="accion" value="eliminar">
                                <input
                                    type="hidden"
                                    name="id"
                                    value="<?= (int) $servicio["id"] ?>"
                                >
                                <button type="submit">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

    <?php else: ?>

        <p>No hay servicios registrados.</p>

    <?php endif; ?>

    <p>
        <a href="../../index.php">Volver al inicio</a>
    </p>

</body>
</html>
