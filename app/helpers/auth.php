<?php

declare(strict_types=1);

function iniciar_sesion(): void
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

function iniciar_sesion_odontologo(): void
{
    iniciar_sesion();
}

function iniciar_sesion_paciente(): void
{
    iniciar_sesion();
}

function redirigir_login(string $destino): void
{
    if (str_starts_with($destino, 'http://') || str_starts_with($destino, 'https://') || str_contains($destino, 'index.php')) {
        redirect_to($destino);
    }

    redirect($destino);
}

function recepcionista_autenticado(): bool
{
    return isset($_SESSION['recepcionista_id']) && ctype_digit((string) $_SESSION['recepcionista_id']);
}

function odontologo_autenticado(): bool
{
    return isset($_SESSION['odontologo_id']) && ctype_digit((string) $_SESSION['odontologo_id']);
}

function paciente_autenticado(): bool
{
    return isset($_SESSION['paciente_id']) && ctype_digit((string) $_SESSION['paciente_id']);
}

function requerir_recepcionista(string $rutaLogin = 'login-recepcionista'): void
{
    iniciar_sesion();

    if (!recepcionista_autenticado()) {
        redirigir_login($rutaLogin);
    }

    token_csrf_recepcionista();
}

function requerir_odontologo(string $rutaLogin = 'login-odontologo'): void
{
    iniciar_sesion();

    if (!odontologo_autenticado()) {
        redirigir_login($rutaLogin);
    }

    token_csrf_odontologo();
}

function requerir_paciente(string $rutaLogin = 'login-paciente', array $parametros = []): void
{
    iniciar_sesion();

    if (!paciente_autenticado()) {
        redirect($rutaLogin, $parametros);
    }
}

function token_csrf_recepcionista(): string
{
    if (!isset($_SESSION['csrf_recepcionista'])) {
        $_SESSION['csrf_recepcionista'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_recepcionista'];
}

function validar_csrf_recepcionista(): bool
{
    $token = $_POST['csrf_token'] ?? '';

    return is_string($token)
        && isset($_SESSION['csrf_recepcionista'])
        && hash_equals($_SESSION['csrf_recepcionista'], $token);
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

function cerrar_sesion_actual(): void
{
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $cookie = session_get_cookie_params();
        setcookie(session_name(), '', [
            'expires' => time() - 42000,
            'path' => $cookie['path'],
            'domain' => $cookie['domain'],
            'secure' => $cookie['secure'],
            'httponly' => $cookie['httponly'],
            'samesite' => $cookie['samesite'] ?? 'Lax',
        ]);
    }

    session_destroy();
}
