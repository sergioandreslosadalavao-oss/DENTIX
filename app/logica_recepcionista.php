<?php
// app/logica_recepcionista.php - Procesamiento de inicio de sesión y registro de recepcionistas
require_once "../config/database.php";
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // CASO 1: INICIO DE SESIÓN
    if (isset($_POST['accion']) && $_POST['accion'] === 'login') {
        $correo = trim($_POST['correo'] ?? '');
        $password = trim($_POST['password'] ?? '');

        if (!empty($correo) && !empty($password)) {
            try {
                $stmt = $conexion->prepare("SELECT id, nombre, correo, password FROM recepcionista WHERE correo = ?");
                $stmt->execute([$correo]);
                $recepcionista = $stmt->fetch();

                if ($recepcionista && (password_verify($password, $recepcionista['password']) || $password === $recepcionista['password'])) {
                    $_SESSION['recepcionista_id'] = $recepcionista['id'];
                    $_SESSION['recepcionista_nombre'] = $recepcionista['nombre'];
                    $_SESSION['recepcionista_correo'] = $recepcionista['correo'];

                    // Redirección directa al Panel de Administración (Vista con Menú Lateral)
                    header("Location: ../view/odontologo/lista_odontologos.php");
                    exit();
                } else {
                    $_SESSION['error'] = "Correo o contraseña incorrectos.";
                    header("Location: ../view/recepcionista/login_recepcionista.php");
                    exit();
                }
            } catch (PDOException $e) {
                $_SESSION['error'] = "Error en la base de datos: " . $e->getMessage();
                header("Location: ../view/recepcionista/login_recepcionista.php");
                exit();
            }
        }
    }

    // CASO 2: REGISTRO DE NUEVO RECEPCIONISTA
    if (isset($_POST['accion']) && $_POST['accion'] === 'registro') {
        $nombre = trim($_POST['nombre'] ?? '');
        $correo = trim($_POST['correo'] ?? '');
        $password = trim($_POST['password'] ?? '');

        if (!empty($nombre) && !empty($correo) && !empty($password)) {
            try {
                $stmt_check = $conexion->prepare("SELECT id FROM recepcionista WHERE correo = ?");
                $stmt_check->execute([$correo]);

                if ($stmt_check->fetch()) {
                    $_SESSION['error'] = "El correo ya se encuentra registrado.";
                    header("Location: ../view/recepcionista/form_recepcionista.php");
                    exit();
                } else {
                    $password_hashed = password_hash($password, PASSWORD_BCRYPT);
                    $stmt = $conexion->prepare("INSERT INTO recepcionista (nombre, correo, password) VALUES (?, ?, ?)");
                    $stmt->execute([$nombre, $correo, $password_hashed]);

                    $_SESSION['recepcionista_id'] = $conexion->lastInsertId();
                    $_SESSION['recepcionista_nombre'] = $nombre;
                    $_SESSION['recepcionista_correo'] = $correo;

                    // Redirección directa al Panel de Administración (Vista con Menú Lateral)
                    header("Location: ../view/odontologo/lista_odontologos.php");
                    exit();
                }
            } catch (PDOException $e) {
                $_SESSION['error'] = "Error al registrar: " . $e->getMessage();
                header("Location: ../view/recepcionista/form_recepcionista.php");
                exit();
            }
        }
    }
}