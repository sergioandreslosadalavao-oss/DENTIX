<?php
require_once __DIR__ . '/../../app/paciente_auth.php';
iniciar_sesion_paciente();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !validar_csrf_paciente()) {
    http_response_code(400);
    exit('Solicitud no válida.');
}

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
header('Location: login_paciente.php');
exit;
