<?php

require_once __DIR__ . "/recepcionista_auth.php";
requerir_recepcionista("../view/recepcionista/lista_recepcionista.php");
require_once __DIR__ . "/../config/database.php";

$metodo = $_SERVER["REQUEST_METHOD"];
$accion = $_GET["accion"] ?? $_POST["accion"] ?? "registrar";

// GENERAR SLUG PUBLICO A PARTIR DEL NOMBRE
function generarSlug(string $nombre): string
{
    $convertido = @iconv("UTF-8", "ASCII//TRANSLIT//IGNORE", $nombre);
    if ($convertido !== false) {
        $nombre = $convertido;
    }
    $nombre = strtolower($nombre);
    $nombre = preg_replace("/[^a-z0-9]+/", "-", $nombre);
    $nombre = trim((string) $nombre, "-");

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
        $sql = "SELECT id, nombre, especialidad, consultorio, slug_publico, email, activo
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

if (!validar_csrf_recepcionista()) {
    $_SESSION["mensaje"] = "La sesión del formulario venció. Recarga la página e inténtalo de nuevo.";
    $_SESSION["tipo_mensaje"] = "error";
    header("Location: ../view/odontologo/lista_odontologos.php");
    exit;
}

try {

    // REGISTRAR ODONTOLOGO
    if ($accion === "registrar") {

        $nombre = trim($_POST["nombre"] ?? "");
        $especialidad = trim($_POST["especialidad"] ?? "");
        $consultorio = trim($_POST["consultorio"] ?? "");
        $email = trim($_POST["email"] ?? "");
        $password = $_POST["password"] ?? "";

        if ($nombre === "" || $especialidad === "" || $email === "" || $password === "") {
            $_SESSION["mensaje"] = "Error: nombre, especialidad, correo y contraseña son obligatorios.";
            $_SESSION["tipo_mensaje"] = "error";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION["mensaje"] = "Error: el correo electrónico no es válido.";
            $_SESSION["tipo_mensaje"] = "error";
        } elseif (strlen($password) < 6 || strlen($password) > 72) {
            $_SESSION["mensaje"] = "Error: la contraseña debe tener entre 6 y 72 caracteres.";
            $_SESSION["tipo_mensaje"] = "error";
        } else {

            $slug = generarSlug($nombre);

            if ($slug === "") {
                $_SESSION["mensaje"] = "Error: no se pudo generar el slug del odontólogo.";
                $_SESSION["tipo_mensaje"] = "error";
            } else {

                // COMPROBAR QUE EL SLUG Y EL EMAIL NO EXISTAN
                $sql = "SELECT id FROM odontologo WHERE slug_publico = :slug";
                $consulta = $conexion->prepare($sql);
                $consulta->execute([":slug" => $slug]);

                $stmtEmail = $conexion->prepare("SELECT id FROM odontologo WHERE lower(email) = lower(:email)");
                $stmtEmail->execute([":email" => $email]);

                if ($consulta->fetch()) {
                    $_SESSION["mensaje"] = "Ya existe un odontólogo con un nombre que genera el mismo slug.";
                    $_SESSION["tipo_mensaje"] = "error";
                } elseif ($stmtEmail->fetch()) {
                    $_SESSION["mensaje"] = "Ya existe un odontólogo registrado con ese correo electrónico.";
                    $_SESSION["tipo_mensaje"] = "error";
                } else {

                    $sql = "INSERT INTO odontologo
                            (nombre, especialidad, consultorio, slug_publico, email, password_hash, activo)
                            VALUES
                            (:nombre, :especialidad, :consultorio, :slug, :email, :password_hash, TRUE)";

                    $consulta = $conexion->prepare($sql);
                    $consulta->execute([
                        ":nombre" => $nombre,
                        ":especialidad" => $especialidad,
                        ":consultorio" => $consultorio === "" ? null : $consultorio,
                        ":slug" => $slug,
                        ":email" => $email,
                        ":password_hash" => password_hash($password, PASSWORD_DEFAULT)
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
        $email = trim($_POST["email"] ?? "");
        $activo = ($_POST["activo"] ?? "1") === "1";

        if ($id === false || $id === null || $id <= 0) {
            $_SESSION["mensaje"] = "Error: el ID del odontólogo no es válido.";
            $_SESSION["tipo_mensaje"] = "error";
        } elseif ($nombre === "") {
            $_SESSION["mensaje"] = "Error: el nombre es obligatorio.";
            $_SESSION["tipo_mensaje"] = "error";
            header("Location: ../view/odontologo/editar_odontologo.php?id=" . $id);
            exit;
        } elseif ($especialidad === "") {
            $_SESSION["mensaje"] = "Error: la especialidad es obligatoria.";
            $_SESSION["tipo_mensaje"] = "error";
            header("Location: ../view/odontologo/editar_odontologo.php?id=" . $id);
            exit;
        } elseif ($email === "" || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION["mensaje"] = "Error: el correo electrónico es obligatorio y debe ser válido.";
            $_SESSION["tipo_mensaje"] = "error";
            header("Location: ../view/odontologo/editar_odontologo.php?id=" . $id);
            exit;
        } else {

            $slug = generarSlug($nombre);

            if ($slug === "") {
                $_SESSION["mensaje"] = "Error: no se pudo generar el slug del odontólogo.";
                $_SESSION["tipo_mensaje"] = "error";
                header("Location: ../view/odontologo/editar_odontologo.php?id=" . $id);
                exit;
            } else {

                // COMPROBAR SLUG Y EMAIL DUPLICADOS EXCLUYENDO EL ACTUAL
                $sql = "SELECT id
                        FROM odontologo
                        WHERE slug_publico = :slug
                        AND id <> :id";
                $consulta = $conexion->prepare($sql);
                $consulta->execute([":slug" => $slug, ":id" => $id]);

                $stmtEmail = $conexion->prepare(
                    "SELECT id FROM odontologo WHERE lower(email) = lower(:email) AND id <> :id"
                );
                $stmtEmail->execute([":email" => $email, ":id" => $id]);

                if ($consulta->fetch()) {
                    $_SESSION["mensaje"] = "Ya existe otro odontólogo con un nombre que genera el mismo slug.";
                    $_SESSION["tipo_mensaje"] = "error";
                    header("Location: ../view/odontologo/editar_odontologo.php?id=" . $id);
                    exit;
                } elseif ($stmtEmail->fetch()) {
                    $_SESSION["mensaje"] = "Ya existe otro odontólogo con ese correo electrónico.";
                    $_SESSION["tipo_mensaje"] = "error";
                    header("Location: ../view/odontologo/editar_odontologo.php?id=" . $id);
                    exit;
                } else {

                    $sql = "UPDATE odontologo
                            SET nombre = :nombre,
                                especialidad = :especialidad,
                                consultorio = :consultorio,
                                slug_publico = :slug,
                                email = :email,
                                activo = :activo
                            WHERE id = :id";

                    $consulta = $conexion->prepare($sql);
                    $consulta->bindValue(":nombre", $nombre, PDO::PARAM_STR);
                    $consulta->bindValue(":especialidad", $especialidad, PDO::PARAM_STR);
                    $consulta->bindValue(":consultorio", $consultorio === "" ? null : $consultorio, $consultorio === "" ? PDO::PARAM_NULL : PDO::PARAM_STR);
                    $consulta->bindValue(":slug", $slug, PDO::PARAM_STR);
                    $consulta->bindValue(":email", $email, PDO::PARAM_STR);
                    $consulta->bindValue(":activo", $activo, PDO::PARAM_BOOL);
                    $consulta->bindValue(":id", $id, PDO::PARAM_INT);
                    $consulta->execute();

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

    if ($e->getCode() === "23503") {
        $_SESSION["mensaje"] =
            "No se puede eliminar el odontólogo porque tiene citas asociadas. Puedes desactivarlo desde Editar.";
    } else {
        $_SESSION["mensaje"] =
            "No se pudo completar la operación. Verifica los datos y las restricciones de la base de datos.";
    }

    $_SESSION["tipo_mensaje"] = "error";
}

// MOSTRAR EL RESULTADO EN EL LISTADO
header("Location: ../view/odontologo/lista_odontologos.php");
exit;
