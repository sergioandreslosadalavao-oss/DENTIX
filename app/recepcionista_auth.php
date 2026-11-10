<?php

function requerir_recepcionista(string $rutaLogin): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    if (!isset($_SESSION['recepcionista_id'])) {
        header('Location: ' . $rutaLogin);
        exit;
    }

    token_csrf_recepcionista();
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
