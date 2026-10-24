
<?php

session_start();

require_once __DIR__ . "/../config/database.php";

$metodo = $_SERVER["REQUEST_METHOD"];
$accion = $_GET["accion"] ?? $_POST["accion"] ?? "registrar";

// CONSULTAR PACIENTE PARA EDITAR
if ($metodo === "GET" && $accion === "editar") {

    $id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

    if ($id === false || $id === null || $id <= 0) {
        $_SESSION["mensaje"] = "Error: el ID del paciente no es válido.";
        $_SESSION["tipo_mensaje"] = "error";

        header("Location: ../view/lista_pacientes.php");
        exit;
    }

    try {
        $sql = "SELECT id, nombre, telefono, email, fecha_nac, alergias
                FROM paciente
                WHERE id = :id";

        $consulta = $conexion->prepare($sql);
        $consulta->execute([":id" => $id]);

        $paciente = $consulta->fetch(PDO::FETCH_ASSOC);

        if (!$paciente) {
            $_SESSION["mensaje"] = "No se encontró el paciente solicitado.";
            $_SESSION["tipo_mensaje"] = "error";

            header("Location: ../view/lista_pacientes.php");
            exit;
        }

        // La lógica obtiene los datos y entrega el control a la vista.
        require __DIR__ . "/../view/editar_paciente.php";
        exit;

    } catch (PDOException $e) {
        error_log("Error al consultar paciente: " . $e->getMessage());

        $_SESSION["mensaje"] = "No se pudieron consultar los datos del paciente.";
        $_SESSION["tipo_mensaje"] = "error";

        header("Location: ../view/lista_pacientes.php");
        exit;
    }
}

// Las demás operaciones se procesan mediante POST.
if ($metodo !== "POST") {
    header("Location: ../view/lista_pacientes.php");
    exit;
}

try {

    // REGISTRAR PACIENTE
    if ($accion === "registrar") {

        $nombre = trim($_POST["nombre"] ?? "");
        $telefono = trim($_POST["telefono"] ?? "");
        $email = trim($_POST["email"] ?? "");
        $fecha_nac = $_POST["fecha_nac"] ?? "";
        $alergias = trim($_POST["alergias"] ?? "");

        if ($nombre === "") {

            $_SESSION["mensaje"] = "Error: el nombre es obligatorio.";
            $_SESSION["tipo_mensaje"] = "error";

        } elseif ($email !== "" &&
                  !filter_var($email, FILTER_VALIDATE_EMAIL)) {

            $_SESSION["mensaje"] = "Error: el correo electrónico no es válido.";
            $_SESSION["tipo_mensaje"] = "error";

        } else {

            // Comprobar si el correo o el teléfono ya existen.
            $sql = "SELECT id
                    FROM paciente
                    WHERE (
                        :email <> ''
                        AND LOWER(TRIM(email)) = LOWER(TRIM(:email_busqueda))
                    )
                    OR (
                        :telefono <> ''
                        AND TRIM(telefono) = TRIM(:telefono_busqueda)
                    )
                    LIMIT 1";

            $consulta = $conexion->prepare($sql);

            $consulta->execute([
                ":email" => $email,
                ":email_busqueda" => $email,
                ":telefono" => $telefono,
                ":telefono_busqueda" => $telefono
            ]);

            if ($consulta->fetch()) {

                $_SESSION["mensaje"] =
                    "No se puede registrar: el correo o el teléfono ya están registrados.";

                $_SESSION["tipo_mensaje"] = "error";

            } else {

                $sql = "INSERT INTO paciente
                        (nombre, telefono, email, fecha_nac, alergias)
                        VALUES
                        (:nombre, :telefono, :email, :fecha_nac, :alergias)";

                $consulta = $conexion->prepare($sql);

                $consulta->execute([
                    ":nombre" => $nombre,
                    ":telefono" => $telefono === "" ? null : $telefono,
                    ":email" => $email === "" ? null : $email,
                    ":fecha_nac" => $fecha_nac === "" ? null : $fecha_nac,
                    ":alergias" => $alergias
                ]);

                $_SESSION["mensaje"] = "Paciente registrado correctamente.";
                $_SESSION["tipo_mensaje"] = "exito";
            }
        }

    // ACTUALIZAR PACIENTE
    } elseif ($accion === "actualizar") {

        $id = filter_var(
            $_POST["id"] ?? null,
            FILTER_VALIDATE_INT
        );

        $nombre = trim($_POST["nombre"] ?? "");
        $telefono = trim($_POST["telefono"] ?? "");
        $email = trim($_POST["email"] ?? "");
        $fecha_nac = $_POST["fecha_nac"] ?? "";
        $alergias = trim($_POST["alergias"] ?? "");

        if ($id === false || $id === null || $id <= 0) {

            $_SESSION["mensaje"] = "Error: el ID del paciente no es válido.";
            $_SESSION["tipo_mensaje"] = "error";

        } elseif ($nombre === "") {

            $_SESSION["mensaje"] = "Error: el nombre es obligatorio.";
            $_SESSION["tipo_mensaje"] = "error";

        } elseif ($email !== "" &&
                  !filter_var($email, FILTER_VALIDATE_EMAIL)) {

            $_SESSION["mensaje"] = "Error: el correo electrónico no es válido.";
            $_SESSION["tipo_mensaje"] = "error";

        } else {

            // Excluir al paciente actual al comprobar duplicados.
            $sql = "SELECT id
                    FROM paciente
                    WHERE id <> :id
                    AND (
                        (
                            :email <> ''
                            AND LOWER(TRIM(email)) = LOWER(TRIM(:email_busqueda))
                        )
                        OR (
                            :telefono <> ''
                            AND TRIM(telefono) = TRIM(:telefono_busqueda)
                        )
                    )
                    LIMIT 1";

            $consulta = $conexion->prepare($sql);

            $consulta->execute([
                ":id" => $id,
                ":email" => $email,
                ":email_busqueda" => $email,
                ":telefono" => $telefono,
                ":telefono_busqueda" => $telefono
            ]);

            if ($consulta->fetch()) {

                $_SESSION["mensaje"] =
                    "No se puede actualizar: el correo o el teléfono ya están registrados en otro paciente.";

                $_SESSION["tipo_mensaje"] = "error";

            } else {

                $sql = "UPDATE paciente
                        SET nombre = :nombre,
                            telefono = :telefono,
                            email = :email,
                            fecha_nac = :fecha_nac,
                            alergias = :alergias
                        WHERE id = :id";

                $consulta = $conexion->prepare($sql);

                $consulta->execute([
                    ":nombre" => $nombre,
                    ":telefono" => $telefono === "" ? null : $telefono,
                    ":email" => $email === "" ? null : $email,
                    ":fecha_nac" => $fecha_nac === "" ? null : $fecha_nac,
                    ":alergias" => $alergias,
                    ":id" => $id
                ]);

                // rowCount() puede ser cero si no hubo cambios.
                $sql = "SELECT id FROM paciente WHERE id = :id";
                $verificacion = $conexion->prepare($sql);
                $verificacion->execute([":id" => $id]);

                if ($verificacion->fetch()) {
                    $_SESSION["mensaje"] =
                        "Paciente actualizado correctamente.";
                    $_SESSION["tipo_mensaje"] = "exito";
                } else {
                    $_SESSION["mensaje"] =
                        "No se encontró el paciente que intentas actualizar.";
                    $_SESSION["tipo_mensaje"] = "error";
                }
            }
        }

    // ELIMINAR PACIENTE
    } elseif ($accion === "eliminar") {

        $id = filter_var(
            $_POST["id"] ?? null,
            FILTER_VALIDATE_INT
        );

        if ($id === false || $id === null || $id <= 0) {

            $_SESSION["mensaje"] = "Error: el ID del paciente no es válido.";
            $_SESSION["tipo_mensaje"] = "error";

        } else {

            $sql = "DELETE FROM paciente WHERE id = :id";

            $consulta = $conexion->prepare($sql);
            $consulta->execute([":id" => $id]);

            if ($consulta->rowCount() > 0) {
                $_SESSION["mensaje"] = "Paciente eliminado correctamente.";
                $_SESSION["tipo_mensaje"] = "exito";
            } else {
                $_SESSION["mensaje"] =
                    "No se encontró el paciente que intentas eliminar.";
                $_SESSION["tipo_mensaje"] = "error";
            }
        }

    } else {

        $_SESSION["mensaje"] = "Error: la acción solicitada no es válida.";
        $_SESSION["tipo_mensaje"] = "error";
    }

} catch (PDOException $e) {

    error_log("Error en logica_paciente.php: " . $e->getMessage());

    $_SESSION["mensaje"] =
        "No se pudo completar la operación. Verifica los datos y las restricciones de la base de datos.";

    $_SESSION["tipo_mensaje"] = "error";
}

// Mostrar el resultado en el listado.
header("Location: ../view/lista_pacientes.php");
exit;
