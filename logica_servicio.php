<?php

session_start();

require_once __DIR__ . "/../config/database.php";

$metodo = $_SERVER["REQUEST_METHOD"];

// Solo se procesan operaciones por POST
if ($metodo !== "POST") {
    header("Location: ../view/servicio/lista_servicios.php");
    exit;
}

$accion = $_POST["accion"] ?? "registrar";

// Pagina a la que se vuelve despues de la operacion
$destino = "../view/servicio/lista_servicios.php";

function mensaje(string $texto, string $tipo): void
{
    $_SESSION["mensaje"] = $texto;
    $_SESSION["tipo_mensaje"] = $tipo;
}

// VALIDAR Y NORMALIZAR LOS CAMPOS (nombre, duracion_min, precio)
// Devuelve [datos, error]
function validarServicio(array $post): array
{
    $nombre = trim($post["nombre"] ?? "");
    $duracion = filter_var(trim($post["duracion_min"] ?? ""), FILTER_VALIDATE_INT);
    $precioTexto = str_replace(",", ".", trim($post["precio"] ?? ""));

    if ($nombre === "") {
        return [null, "Error: el nombre es obligatorio."];
    }

    if (preg_match_all("/./su", $nombre) > 80) {
        return [null, "Error: el nombre no puede superar los 80 caracteres."];
    }

    if ($duracion === false || $duracion === null) {
        return [null, "Error: la duración debe ser un número entero de minutos."];
    }

    if ($duracion < 15) {
        return [null, "Error: la duración mínima es de 15 minutos."];
    }

    if ($duracion > 1440) {
        return [null, "Error: la duración no es válida."];
    }

    if ($precioTexto === "") {
        $precioTexto = "0";
    }

    if (!is_numeric($precioTexto)) {
        return [null, "Error: el precio debe ser un número."];
    }

    $precio = round((float) $precioTexto, 2);

    if ($precio < 0) {
        return [null, "Error: el precio no puede ser negativo."];
    }

    // NUMERIC(10,2) admite hasta 99.999.999,99
    if ($precio > 99999999.99) {
        return [null, "Error: el precio es demasiado alto."];
    }

    return [
        [
            "nombre" => $nombre,
            "duracion_min" => $duracion,
            "precio" => number_format($precio, 2, ".", "")
        ],
        null
    ];
}

try {

    // REGISTRAR SERVICIO
    if ($accion === "registrar") {

        [$datos, $error] = validarServicio($_POST);

        if ($error !== null) {

            mensaje($error, "error");
            $destino = "../view/servicio/form_servicio.php";

        } else {

            // COMPROBAR QUE EL NOMBRE NO EXISTA
            $consulta = $conexion->prepare(
                "SELECT id FROM servicio WHERE lower(nombre) = lower(:nombre)"
            );
            $consulta->execute([":nombre" => $datos["nombre"]]);

            if ($consulta->fetch()) {

                mensaje("Error: ya existe un servicio con ese nombre.", "error");
                $destino = "../view/servicio/form_servicio.php";

            } else {

                $consulta = $conexion->prepare(
                    "INSERT INTO servicio (nombre, duracion_min, precio)
                     VALUES (:nombre, :duracion, :precio)"
                );
                $consulta->execute([
                    ":nombre" => $datos["nombre"],
                    ":duracion" => $datos["duracion_min"],
                    ":precio" => $datos["precio"]
                ]);

                mensaje("Servicio registrado correctamente.", "exito");
            }
        }

    // ACTUALIZAR SERVICIO
    } elseif ($accion === "actualizar") {

        $id = filter_var($_POST["id"] ?? null, FILTER_VALIDATE_INT);

        if ($id === false || $id === null || $id <= 0) {

            mensaje("Error: el ID del servicio no es válido.", "error");

        } else {

            [$datos, $error] = validarServicio($_POST);

            if ($error !== null) {

                mensaje($error, "error");
                $destino = "../view/servicio/editar_servicio.php?id=" . $id;

            } else {

                // COMPROBAR NOMBRE DUPLICADO EXCLUYENDO EL ACTUAL
                $consulta = $conexion->prepare(
                    "SELECT id FROM servicio
                     WHERE lower(nombre) = lower(:nombre) AND id <> :id"
                );
                $consulta->execute([
                    ":nombre" => $datos["nombre"],
                    ":id" => $id
                ]);

                if ($consulta->fetch()) {

                    mensaje("Error: ya existe otro servicio con ese nombre.", "error");
                    $destino = "../view/servicio/editar_servicio.php?id=" . $id;

                } else {

                    $consulta = $conexion->prepare(
                        "UPDATE servicio
                         SET nombre = :nombre,
                             duracion_min = :duracion,
                             precio = :precio
                         WHERE id = :id"
                    );
                    $consulta->execute([
                        ":nombre" => $datos["nombre"],
                        ":duracion" => $datos["duracion_min"],
                        ":precio" => $datos["precio"],
                        ":id" => $id
                    ]);

                    if ($consulta->rowCount() > 0) {
                        mensaje("Servicio actualizado correctamente.", "exito");
                    } else {
                        mensaje("No se encontró el servicio que intentas actualizar.", "error");
                    }
                }
            }
        }

    // ELIMINAR SERVICIO
    } elseif ($accion === "eliminar") {

        $id = filter_var($_POST["id"] ?? null, FILTER_VALIDATE_INT);

        if ($id === false || $id === null || $id <= 0) {

            mensaje("Error: el ID del servicio no es válido.", "error");

        } else {

            $consulta = $conexion->prepare("DELETE FROM servicio WHERE id = :id");
            $consulta->execute([":id" => $id]);

            if ($consulta->rowCount() > 0) {
                mensaje("Servicio eliminado correctamente.", "exito");
            } else {
                mensaje("No se encontró el servicio que intentas eliminar.", "error");
            }
        }

    } else {

        mensaje("Error: la acción solicitada no es válida.", "error");
    }

} catch (PDOException $e) {

    error_log("Error en logica_servicio.php: " . $e->getMessage());

    $codigo = $e->getCode();

    if ($codigo === "23503") {
        // Llave foranea: cita o detalle_cita usan este servicio (ON DELETE RESTRICT)
        mensaje(
            "No se puede eliminar el servicio porque está asociado a citas registradas.",
            "error"
        );
    } elseif ($codigo === "23505") {
        mensaje("Error: ya existe un servicio con ese nombre.", "error");
    } else {
        mensaje(
            "No se pudo completar la operación. Verifica los datos y las restricciones de la base de datos.",
            "error"
        );
    }
}

header("Location: " . $destino);
exit;
