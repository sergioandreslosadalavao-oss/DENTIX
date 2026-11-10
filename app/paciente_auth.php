<?php

function iniciar_sesion_paciente(): void
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

function token_csrf_paciente(): string
{
    if (!isset($_SESSION['csrf_paciente'])) {
        $_SESSION['csrf_paciente'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_paciente'];
}

function validar_csrf_paciente(): bool
{
    $token = $_POST['csrf_token'] ?? '';

    return is_string($token)
        && isset($_SESSION['csrf_paciente'])
        && hash_equals($_SESSION['csrf_paciente'], $token);
}

function paciente_autenticado(): bool
{
    return isset($_SESSION['paciente_id']) && ctype_digit((string) $_SESSION['paciente_id']);
}
