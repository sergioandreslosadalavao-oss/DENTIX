<?php

declare(strict_types=1);

require_once APP_PATH . '/models/recepcionista_model.php';

function recepcionista_login(): void
{
    iniciar_sesion();

    if (recepcionista_autenticado()) {
        redirect('citas');
    }

    $error = error_get() ?? '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $email = trim($_POST['email'] ?? '');
        $password = trim($_POST['password'] ?? '');

        if ($email !== '' && $password !== '') {
            try {
                $recepcionista = recepcionista_buscar_por_email($email);

                if ($recepcionista && password_verify($password, $recepcionista['password_hash'])) {
                    session_regenerate_id(true);
                    $_SESSION['recepcionista_id'] = (string) $recepcionista['id'];
                    $_SESSION['recepcionista_nombre'] = $recepcionista['nombre'];
                    $_SESSION['recepcionista_correo'] = $recepcionista['email'];
                    redirect('citas');
                }

                $error = 'Credenciales incorrectas. Verifique correo y contraseña.';
            } catch (PDOException $e) {
                error_log('Error en el inicio de sesión de recepción: ' . $e->getMessage());
                $error = 'Error al conectar con la base de datos.';
            }
        } else {
            $error = 'Por favor diligencie todos los campos de inicio de sesión.';
        }
    }

    render('recepcionista/login', compact('error'));
}

function recepcionista_registro(): void
{
    requerir_recepcionista('login-recepcionista');

    $error = error_get() ?? '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nombre = trim($_POST['nombre'] ?? '');
        $telefono = trim($_POST['telefono'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = trim($_POST['password'] ?? '');

        if ($nombre !== '' && $telefono !== '' && filter_var($email, FILTER_VALIDATE_EMAIL) && $password !== '') {
            try {
                if (recepcionista_email_existe($email)) {
                    $error = 'El correo ya se encuentra registrado.';
                } else {
                    recepcionista_crear($nombre, $telefono, $email, password_hash($password, PASSWORD_DEFAULT));
                    flash_set('La cuenta de recepción fue creada.', 'exito');
                    redirect('citas');
                }
            } catch (PDOException $e) {
                error_log('No se pudo crear la cuenta de recepción: ' . $e->getMessage());
                $error = 'No se pudo crear la cuenta. Verifica los datos e inténtalo de nuevo.';
            }
        } else {
            $error = 'Completa todos los campos con datos válidos.';
        }
    }

    render('recepcionista/form', compact('error'));
}

function recepcionista_logout(): void
{
    iniciar_sesion();

    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !validar_csrf_recepcionista()) {
        http_response_code(400);
        exit('Solicitud no válida.');
    }

    cerrar_sesion_actual();
    redirect('login-recepcionista');
}
