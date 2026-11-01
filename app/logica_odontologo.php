
<?php

session_start();

require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../app/sesion.php";

$metodo = $_SERVER["REQUEST_METHOD"];
$accion = $_GET["accion"] ?? $_POST["accion"] ?? "registrar";

// LOGIN ODONTOLOGO
// Autentica por email + password (bcrypt), guarda la sesión POR ROL y evita
// la fijación de sesión regenerando el id DESPUÉS de validar credenciales.
if ($metodo === "POST" && $accion === "login_odontologo") {

    $email = strtolower(trim($_POST["email"] ?? ""));
    $clave = $_POST["password"] ?? "";

    if ($email === "" || $clave === "") {
        sesion_denegar("../view/odontologo/login_odontologo.php", "Ingresa tu correo y contraseña.");
    }

    try {
        $sql = "SELECT id, nombre, email, password_hash, activo, cargo
                FROM odontologo
                WHERE lower(email) = :email";
        $consulta = $conexion->prepare($sql);
        $consulta->execute([":email" => $email]);
        $odontologo = $consulta->fetch(PDO::FETCH_ASSOC);

        if (!$odontologo || !password_verify($clave, $odontologo["password_hash"])) {
            sesion_denegar("../view/odontologo/login_odontologo.php", "Correo o contraseña incorrectos.");
        }

        if (!$odontologo["activo"]) {
            sesion_denegar("../view/odontologo/login_odontologo.php", "Tu cuenta está desactivada. Consulta al odontólogo líder.");
        }

        if ($odontologo["cargo"] !== 'lider') {
            // El módulo clínico del asociado aún no está construido:
            // sin pantalla asignada, sin acceso. Hoy solo ingresa el líder.
            sesion_denegar("../view/odontologo/login_odontologo.php", "El módulo clínico para odontólogos asociados aún no está disponible.");
        }

        $consulta = $conexion->prepare(
            "UPDATE odontologo SET ultimo_acceso = CURRENT_TIMESTAMP WHERE id = :id"
        );
        $consulta->execute([":id" => $odontologo["id"]]);

        session_regenerate_id(true);
        $_SESSION["odontologo_id"] = (int) $odontologo["id"];
        $_SESSION["odontologo_nombre"] = $odontologo["nombre"];
        $_SESSION["odontologo_cargo"] = $odontologo["cargo"];

        header("Location: ../view/odontologo/lista_odontologos.php");
        exit;

    } catch (PDOException $e) {
        error_log("Error al iniciar sesión de odontólogo: " . $e->getMessage());
        sesion_denegar("../view/odontologo/login_odontologo.php", "No se pudo iniciar sesión. Inténtalo de nuevo.");
    }
}

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

    // RN-07: la edición de odontólogos exige sesión de líder activo
    sesion_requiere_odontologo_lider("../view/odontologo/login_odontologo.php");

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

// RN-07: TODAS las acciones de gestión de odontólogos (registrar, actualizar,
// eliminar) exigen sesión de odontólogo LÍDER activo, revalidada contra la BD
// dentro de la guarda. El formulario ya no es la barrera: esta es la puerta.
if (in_array($accion, ["registrar", "actualizar", "eliminar"], true)) {
    sesion_requiere_odontologo_lider("../view/odontologo/login_odontologo.php");
}

try {

    // REGISTRAR ODONTOLOGO
    // El registro incluye credenciales de acceso (email + password_hash),
    // porque el schema v2.1+ los exige NOT NULL. Solo se ingresa como
    // 'asociado' (DEFAULT del esquema): dar de alta otro líder no es un flujo.
    if ($accion === "registrar") {

        $nombre = trim($_POST["nombre"] ?? "");
        $especialidad = trim($_POST["especialidad"] ?? "");
        $consultorio = trim($_POST["consultorio"] ?? "");
        $email = strtolower(trim($_POST["email"] ?? ""));
        $clave = $_POST["password"] ?? "";

        if ($nombre === "") {

            $_SESSION["mensaje"] = "Error: el nombre es obligatorio.";
            $_SESSION["tipo_mensaje"] = "error";

        } elseif ($especialidad === "") {

            $_SESSION["mensaje"] = "Error: la especialidad es obligatoria.";
            $_SESSION["tipo_mensaje"] = "error";

        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

            $_SESSION["mensaje"] = "Error: el correo electrónico no es válido.";
            $_SESSION["tipo_mensaje"] = "error";

        } elseif (strlen($clave) < 8) {

            $_SESSION["mensaje"] = "Error: la contraseña debe tener al menos 8 caracteres.";
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

                    $password_hash = password_hash($clave, PASSWORD_BCRYPT, ["cost" => 12]);

                    $sql = "INSERT INTO odontologo
                            (nombre, especialidad, consultorio, slug_publico, email, password_hash)
                            VALUES
                            (:nombre, :especialidad, :consultorio, :slug, :email, :password_hash)";

                    try {
                        $consulta = $conexion->prepare($sql);
                        $consulta->execute([
                            ":nombre" => $nombre,
                            ":especialidad" => $especialidad,
                            ":consultorio" => $consultorio === "" ? null : $consultorio,
                            ":slug" => $slug,
                            ":email" => $email,
                            ":password_hash" => $password_hash
                        ]);

                        $_SESSION["mensaje"] = "Odontólogo asociado registrado correctamente. Ya puede usar sus credenciales cuando el módulo clínico esté disponible.";
                        $_SESSION["tipo_mensaje"] = "exito";
                    } catch (PDOException $e) {
                        if ($e->getCode() === "23505") {
                            // UNIQUE de email: el correo ya está en uso
                            $_SESSION["mensaje"] = "Error: ese correo electrónico ya está registrado.";
                        } else {
                            throw $e;
                        }
                        $_SESSION["tipo_mensaje"] = "error";
                    }
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

    // DESHABILITAR ODONTOLOGO (baja lógica, no DELETE físico)
    // fk_cita_odontologo es ON DELETE RESTRICT: un DELETE fallaría para
    // cualquier odontólogo con citas. La baja correcta es un soft-delete
    // (activo = FALSE): deja de recibir asignaciones y conserva el historial.
    } elseif ($accion === "eliminar") {

        $id = filter_var($_POST["id"] ?? null, FILTER_VALIDATE_INT);

        if ($id === false || $id === null || $id <= 0) {

            $_SESSION["mensaje"] = "Error: el ID del odontólogo no es válido.";
            $_SESSION["tipo_mensaje"] = "error";

        } else {

            // RN-08: el líder no puede deshabilitarse. Primero debe transferir
            // el cargo a otro odontólogo (la baja límites es al nivel de datos).
            $consulta = $conexion->prepare("SELECT cargo FROM odontologo WHERE id = :id");
            $consulta->execute([":id" => $id]);
            $cargoDestino = $consulta->fetchColumn();

            if ($cargoDestino === 'lider') {

                $_SESSION["mensaje"] = "RN-08: el odontólogo líder no puede deshabilitarse. Primero transfiera el cargo.";
                $_SESSION["tipo_mensaje"] = "error";

            } else {

                $consulta = $conexion->prepare(
                    "UPDATE odontologo SET activo = FALSE WHERE id = :id AND cargo <> 'lider'"
                );
                $consulta->execute([":id" => $id]);

                if ($consulta->rowCount() > 0) {
                    $_SESSION["mensaje"] = "Odontólogo deshabilitado. Ya no recibirá nuevas asignaciones; su historial se conserva.";
                    $_SESSION["tipo_mensaje"] = "exito";
                } else {
                    $_SESSION["mensaje"] = "No se encontró el odontólogo que intentas deshabilitar.";
                    $_SESSION["tipo_mensaje"] = "error";
                }
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
