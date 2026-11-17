<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/app/bootstrap.php';

$rutas = require BASE_PATH . '/routes/web.php';
$ruta = isset($_GET['ruta']) ? trim((string) $_GET['ruta'], '/') : 'inicio';

if (!isset($rutas[$ruta])) {
    http_response_code(404);
    render('errors/404');
    exit;
}

[$archivoControlador, $funcionControlador] = $rutas[$ruta];
$archivoControlador = APP_PATH . '/controllers/' . $archivoControlador;

if (!is_file($archivoControlador)) {
    http_response_code(500);
    exit('No se encontró el controlador de la ruta solicitada.');
}

require_once $archivoControlador;

if (!function_exists($funcionControlador)) {
    http_response_code(500);
    exit('No se encontró la función del controlador solicitado.');
}

$funcionControlador();
