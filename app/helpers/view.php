<?php

declare(strict_types=1);

function e(mixed $valor): string
{
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
}

function render(string $vista, array $datos = []): void
{
    $archivo = VIEW_PATH . '/' . ltrim($vista, '/') . '.php';

    if (!is_file($archivo)) {
        http_response_code(500);
        exit('No se encontró la vista solicitada: ' . e($vista));
    }

    extract($datos, EXTR_SKIP);
    require $archivo;
}

function view_path(string $vista): string
{
    return VIEW_PATH . '/' . ltrim($vista, '/') . '.php';
}
