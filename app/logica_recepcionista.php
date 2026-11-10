<?php
// app/logica_recepcionista.php - Procesamiento de inicio de sesión y registro de recepcionistas
require_once "../config/database.php";
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // CASO 1: INICIO DE SESIÓN
    if (isset($_POST['accion']) && $_POST['accion'] === 'login') {
        $correo = trim($_POST['email'] ?? $_POST['correo'] ?? '');
        $password = trim($_POST['password'] ?? '');

        if (!empty($correo) && !empty($password)) {
            try {
                $stmt = $conexion->prepare(
                    "SELECT id, nombre, email, password_hash
                     FROM recepcionista
                     WHERE email = ? AND activo = TRUE"
                );
                $stmt->execute([$correo]);
                $recepcionista = $stmt->fetch();

                if ($recepcionista && password_verify($password, $recepcionista['password_hash'])) {
                    session_regenerate_id(true);
                    $_SESSION['recepcionista_id'] = $recepcionista['id'];
                    $_SESSION['recepcionista_nombre'] = $recepcionista['nombre'];
                    $_SESSION['recepcionista_correo'] = $recepcionista['email'];

                    header("Location: ../view/cita/lista_citas.php");
                    exit();
                } else {
                    $_SESSION['error'] = "Correo o contraseña incorrectos.";
                    header("Location: ../view/recepcionista/lista_recepcionista.php");
                    exit();
                }
            } catch (PDOException $e) {
                error_log("Error en el inicio de sesión de recepción: " . $e->getMessage());
                $_SESSION['error'] = "No se pudo iniciar sesión. Inténtalo de nuevo.";
                header("Location: ../view/recepcionista/lista_recepcionista.php");
                exit();
            }
        } else {
            $_SESSION['error'] = "Correo o contraseña incorrectos.";
            header("Location: ../view/recepcionista/lista_recepcionista.php");
            exit();
        }
    }

    // CASO 2: REGISTRO DE NUEVO RECEPCIONISTA
    if (isset($_POST['accion']) && $_POST['accion'] === 'registro') {
        require_once __DIR__ . '/recepcionista_auth.php';
        requerir_recepcionista('../view/recepcionista/lista_recepcionista.php');

        $nombre = trim($_POST['nombre'] ?? '');
        $telefono = trim($_POST['telefono'] ?? '');
        $correo = trim($_POST['email'] ?? $_POST['correo'] ?? '');
        $password = trim($_POST['password'] ?? '');

        if ($nombre !== '' && $telefono !== '' && filter_var($correo, FILTER_VALIDATE_EMAIL) && $password !== '') {
            try {
                $stmt_check = $conexion->prepare("SELECT id FROM recepcionista WHERE email = ?");
                $stmt_check->execute([$correo]);

                if ($stmt_check->fetch()) {
                    $_SESSION['error'] = "El correo ya se encuentra registrado.";
                    header("Location: ../view/recepcionista/form_recepcionista.php");
                    exit();
                } else {
                    $password_hashed = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $conexion->prepare(
                        "INSERT INTO recepcionista (nombre, telefono, email, password_hash)
                         VALUES (?, ?, ?, ?)"
                    );
                    $stmt->execute([$nombre, $telefono, $correo, $password_hashed]);

                    $_SESSION['mensaje'] = "La cuenta de recepción fue creada.";
                    header("Location: ../view/cita/lista_citas.php");
                    exit();
                }
            } catch (PDOException $e) {
                error_log("No se pudo registrar la cuenta de recepción: " . $e->getMessage());
                $_SESSION['error'] = "No se pudo crear la cuenta. Verifica los datos e inténtalo de nuevo.";
                header("Location: ../view/recepcionista/form_recepcionista.php");
                exit();
            }
        } else {
            $_SESSION['error'] = "Completa todos los campos con datos válidos.";
            header("Location: ../view/recepcionista/form_recepcionista.php");
            exit();
        }
    }
}