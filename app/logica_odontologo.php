
<?php

require_once __DIR__ . "/recepcionista_auth.php";
requerir_recepcionista("../view/recepcionista/lista_recepcionista.php");
require_once __DIR__ . "/../config/database.php";

$metodo = $_SERVER["REQUEST_METHOD"];
$accion = $_GET["accion"] ?? $_POST["accion"] ?? "registrar";

// GENERAR SLUG PUBLICO A PARTIR DEL NOMBRE
function generarSlug(string $nombre): string
{
    $nombre = iconv("UTF-8", "ASCII//TRANSLIT//IGNORE", $nombre);
    $nombre = strtolower($nombre);
    $nombre = preg_replace("/[^a-z0-9]+/", "-", $nombre);
    $nombre = trim($nombre, "-");

    return substr($nombre, 0, 60);
}

// CONSULTAR ODONTOLOGO PARA EDITAR
if ($metodo === "GET" && $accion === "editar") {

    $id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

    if ($id === false || $id === null || $id <= 0) {
        $_SESSION["mensaje"] = "Error: el ID del odontólogo no es válido.";
        $_SESSION["tipo_mensaje"] = "error";

        header("Location: ../view/odontologo/lista_odontologos.php");
        exit;
    }

    try {
        $sql = "SELECT id, nombre, especialidad, consultorio, slug_publico
                FROM odontologo
                WHERE id = :id";

        $consulta = $conexion->prepare($sql);
        $consulta->execute([":id" => $id]);

        $odontologo = $consulta->fetch(PDO::FETCH_ASSOC);

        if (!$odontologo) {
            $_SESSION["mensaje"] = "No se encontró el odontólogo solicitado.";
            $_SESSION["tipo_mensaje"] = "error";

            header("Location: ../view/odontologo/lista_odontologos.php");
            exit;
        }

        require __DIR__ . "/../view/odontologo/editar_odontologo.php";
        exit;

    } catch (PDOException $e) {
        error_log("Error al consultar odontólogo: " . $e->getMessage());

        $_SESSION["mensaje"] = "No se pudieron consultar los datos del odontólogo.";
        $_SESSION["tipo_mensaje"] = "error";

        header("Location: ../view/odontologo/lista_odontologos.php");
        exit;
    }
}

// LAS DEMAS OPERACIONES SE PROCESAN MEDIANTE POST
if ($metodo !== "POST") {
    header("Location: ../view/odontologo/lista_odontologos.php");
    exit;
}

try {

    // REGISTRAR ODONTOLOGO
    if ($accion === "registrar") {

        $nombre = trim($_POST["nombre"] ?? "");
        $especialidad = trim($_POST["especialidad"] ?? "");
        $consultorio = trim($_POST["consultorio"] ?? "");

        if ($nombre === "") {

            $_SESSION["mensaje"] = "Error: el nombre es obligatorio.";
            $_SESSION["tipo_mensaje"] = "error";

        } elseif ($especialidad === "") {

            $_SESSION["mensaje"] = "Error: la especialidad es obligatoria.";
            $_SESSION["tipo_mensaje"] = "error";

        } else {

            $slug = generarSlug($nombre);

            if ($slug === "") {
                $_SESSION["mensaje"] = "Error: no se pudo generar el slug del odontólogo.";
                $_SESSION["tipo_mensaje"] = "error";
            } else {

                // COMPROBAR QUE EL SLUG NO EXISTA
                $sql = "SELECT id FROM odontologo WHERE slug_publico = :slug";
                $consulta = $conexion->prepare($sql);
                $consulta->execute([":slug" => $slug]);

                if ($consulta->fetch()) {
                    $_SESSION["mensaje"] =
                        "Ya existe un odontólogo con un nombre que genera el mismo slug.";
                    $_SESSION["tipo_mensaje"] = "error";
                } else {

                    $sql = "INSERT INTO odontologo
                            (nombre, especialidad, consultorio, slug_publico)
                            VALUES
                            (:nombre, :especialidad, :consultorio, :slug)";

                    $consulta = $conexion->prepare($sql);
                    $consulta->execute([
                        ":nombre" => $nombre,
                        ":especialidad" => $especialidad,
                        ":consultorio" => $consultorio === "" ? null : $consultorio,
                        ":slug" => $slug
                    ]);

                    $_SESSION["mensaje"] = "Odontólogo registrado correctamente.";
                    $_SESSION["tipo_mensaje"] = "exito";
                }
            }
        }

    // ACTUALIZAR ODONTOLOGO
    } elseif ($accion === "actualizar") {

        $id = filter_var($_POST["id"] ?? null, FILTER_VALIDATE_INT);
        $nombre = trim($_POST["nombre"] ?? "");
        $especialidad = trim($_POST["especialidad"] ?? "");
        $consultorio = trim($_POST["consultorio"] ?? "");

        if ($id === false || $id === null || $id <= 0) {

            $_SESSION["mensaje"] = "Error: el ID del odontólogo no es válido.";
            $_SESSION["tipo_mensaje"] = "error";

        } elseif ($nombre === "") {

            $_SESSION["mensaje"] = "Error: el nombre es obligatorio.";
            $_SESSION["tipo_mensaje"] = "error";

        } elseif ($especialidad === "") {

            $_SESSION["mensaje"] = "Error: la especialidad es obligatoria.";
            $_SESSION["tipo_mensaje"] = "error";

        } else {

            $slug = generarSlug($nombre);

            if ($slug === "") {
                $_SESSION["mensaje"] = "Error: no se pudo generar el slug del odontólogo.";
                $_SESSION["tipo_mensaje"] = "error";
            } else {

                // COMPROBAR SLUG DUPLICADO EXCLUYENDO EL ACTUAL
                $sql = "SELECT id
                        FROM odontologo
                        WHERE slug_publico = :slug
                        AND id <> :id";

                $consulta = $conexion->prepare($sql);
                $consulta->execute([
                    ":slug" => $slug,
                    ":id" => $id
                ]);

                if ($consulta->fetch()) {
                    $_SESSION["mensaje"] =
                        "Ya existe otro odontólogo con un nombre que genera el mismo slug.";
                    $_SESSION["tipo_mensaje"] = "error";
                } else {

                    $sql = "UPDATE odontologo
                            SET nombre = :nombre,
                                especialidad = :especialidad,
                                consultorio = :consultorio,
                                slug_publico = :slug
                            WHERE id = :id";

                    $consulta = $conexion->prepare($sql);
                    $consulta->execute([
                        ":nombre" => $nombre,
                        ":especialidad" => $especialidad,
                        ":consultorio" => $consultorio === "" ? null : $consultorio,
                        ":slug" => $slug,
                        ":id" => $id
                    ]);

                    // COMPROBAR SI EL ODONTOLOGO EXISTE
                    $sql = "SELECT id FROM odontologo WHERE id = :id";
                    $verificacion = $conexion->prepare($sql);
                    $verificacion->execute([":id" => $id]);

                    if ($verificacion->fetch()) {
                        $_SESSION["mensaje"] = "Odontólogo actualizado correctamente.";
                        $_SESSION["tipo_mensaje"] = "exito";
                    } else {
                        $_SESSION["mensaje"] = "No se encontró el odontólogo que intentas actualizar.";
                        $_SESSION["tipo_mensaje"] = "error";
                    }
                }
            }
        }

    // ELIMINAR ODONTOLOGO
    } elseif ($accion === "eliminar") {

        $id = filter_var($_POST["id"] ?? null, FILTER_VALIDATE_INT);

        if ($id === false || $id === null || $id <= 0) {

            $_SESSION["mensaje"] = "Error: el ID del odontólogo no es válido.";
            $_SESSION["tipo_mensaje"] = "error";

        } else {

            $sql = "DELETE FROM odontologo WHERE id = :id";
            $consulta = $conexion->prepare($sql);
            $consulta->execute([":id" => $id]);

            if ($consulta->rowCount() > 0) {
                $_SESSION["mensaje"] = "Odontólogo eliminado correctamente.";
                $_SESSION["tipo_mensaje"] = "exito";
            } else {
                $_SESSION["mensaje"] = "No se encontró el odontólogo que intentas eliminar.";
                $_SESSION["tipo_mensaje"] = "error";
            }
        }

    } else {

        $_SESSION["mensaje"] = "Error: la acción solicitada no es válida.";
        $_SESSION["tipo_mensaje"] = "error";
    }

} catch (PDOException $e) {

    error_log("Error en logica_odontologo.php: " . $e->getMessage());

    $_SESSION["mensaje"] =
        "No se pudo completar la operación. Verifica los datos y las restricciones de la base de datos.";

    $_SESSION["tipo_mensaje"] = "error";
}

// MOSTRAR EL RESULTADO EN EL LISTADO
header("Location: ../view/odontologo/lista_odontologos.php");
exit;
