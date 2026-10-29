<?php
require_once __DIR__ . '/../config/database.php';
session_start();

$accion = $_POST['accion'] ?? '';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../view/recepcionista/login_recepcionista.php');
    exit();
}

if ($accion === 'login') {
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $_SESSION['error'] = 'Ingresa tu correo y contraseña.';
        header('Location: ../view/recepcionista/login_recepcionista.php');
        exit();
    }

    try {
        $stmt = $conexion->prepare(
            'SELECT id, nombre, email, password_hash FROM recepcionista WHERE email = ? AND activo = TRUE'
        );
        $stmt->execute([$email]);
        $recepcionista = $stmt->fetch();

        if (!$recepcionista || !password_verify($password, $recepcionista['password_hash'])) {
            $_SESSION['error'] = 'Correo o contraseña incorrectos.';
            header('Location: ../view/recepcionista/login_recepcionista.php');
            exit();
        }

        $stmt = $conexion->prepare('UPDATE recepcionista SET ultimo_acceso = CURRENT_TIMESTAMP WHERE id = ?');
        $stmt->execute([$recepcionista['id']]);

        session_regenerate_id(true);
        $_SESSION['recepcionista_id'] = $recepcionista['id'];
        $_SESSION['recepcionista_nombre'] = $recepcionista['nombre'];
        $_SESSION['recepcionista_email'] = $recepcionista['email'];

        header('Location: ../view/cita/lista_citas.php');
        exit();
    } catch (PDOException $e) {
        error_log('Error al iniciar sesión de recepción: ' . $e->getMessage());
        $_SESSION['error'] = 'No se pudo iniciar sesión. Inténtalo de nuevo.';
        header('Location: ../view/recepcionista/login_recepcionista.php');
        exit();
    }
}

if ($accion === 'registro') {
    $nombre = trim($_POST['nombre'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';

    if ($nombre === '' || $telefono === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
        $_SESSION['error'] = 'Completa todos los campos con datos válidos.';
        header('Location: ../view/recepcionista/form_recepcionista.php');
        exit();
    }

    try {
        $stmt = $conexion->prepare(
            'INSERT INTO recepcionista (nombre, telefono, email, password_hash) VALUES (?, ?, ?, ?) RETURNING id'
        );
        $stmt->execute([$nombre, $telefono, $email, password_hash($password, PASSWORD_DEFAULT)]);
        $recepcionistaId = $stmt->fetchColumn();

        session_regenerate_id(true);
        $_SESSION['recepcionista_id'] = $recepcionistaId;
        $_SESSION['recepcionista_nombre'] = $nombre;
        $_SESSION['recepcionista_email'] = $email;

        header('Location: ../view/cita/lista_citas.php');
        exit();
    } catch (PDOException $e) {
        error_log('Error al registrar recepcionista: ' . $e->getMessage());
        $_SESSION['error'] = 'No se pudo crear la cuenta. Verifica si el correo ya está registrado.';
        header('Location: ../view/recepcionista/form_recepcionista.php');
        exit();
    }
}

header('Location: ../view/recepcionista/login_recepcionista.php');
exit();