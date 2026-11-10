<?php

function iniciar_sesion_odontologo(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    session_set_cookie_params([
        'httponly' => true,
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'samesite' => 'Lax',
    ]);
    session_start();
}

function requerir_odontologo(string $rutaLogin): void
{
    iniciar_sesion_odontologo();

    if (!isset($_SESSION['odontologo_id']) || !ctype_digit((string) $_SESSION['odontologo_id'])) {
        header('Location: ' . $rutaLogin);
        exit;
    }

    token_csrf_odontologo();
}

function token_csrf_odontologo(): string
{
    if (!isset($_SESSION['csrf_odontologo'])) {
        $_SESSION['csrf_odontologo'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_odontologo'];
}

function validar_csrf_odontologo(): bool
{
    $token = $_POST['csrf_token'] ?? '';

    return is_string($token)
        && isset($_SESSION['csrf_odontologo'])
        && hash_equals($_SESSION['csrf_odontologo'], $token);
}

