<?php

declare(strict_types=1);

function base_url(string $path = ''): string
{
    $path = ltrim($path, '/');

    return BASE_URL . ($path === '' ? '' : '/' . $path);
}

function url(string $ruta = 'inicio', array $parametros = []): string
{
    $query = array_merge(['ruta' => $ruta], $parametros);

    return base_url('index.php?' . http_build_query($query));
}

function asset(string $path): string
{
    return base_url('assets/' . ltrim($path, '/'));
}

function redirect(string $ruta = 'inicio', array $parametros = []): void
{
    header('Location: ' . url($ruta, $parametros));
    exit;
}

function redirect_to(string $destino): void
{
    header('Location: ' . $destino);
    exit;
}
