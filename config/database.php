<?php

$rutaEnv = __DIR__ . '/../.env';

if (!is_readable($rutaEnv)) {
    error_log('No se encontró el archivo .env en la raíz del proyecto.');
    exit('Falta la configuración (.env).');
}

// Parser mínimo de .env: CLAVE=valor, ignora comentarios (#) y líneas vacías.
$env = [];
foreach (file($rutaEnv, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $linea) {
    $linea = trim($linea);
    if ($linea === '' || strpos($linea, '#') === 0) {
        continue;
    }
    $partes = explode('=', $linea, 2);
    if (count($partes) === 2) {
        $env[trim($partes[0])] = trim($partes[1]);
    }
}

$host     = $env['DB_HOST']     ?? '127.0.0.1';
$port     = $env['DB_PORT']     ?? '5432';
$dbname   = $env['DB_NAME']     ?? 'sco';
$user     = $env['DB_USER']     ?? 'sergio';
$password = $env['DB_PASSWORD'] ?? '';

try {
    $conexion = new PDO(
        "pgsql:host=$host;port=$port;dbname=$dbname",
        $user,
        $password
    );

    $conexion->setAttribute(
        PDO::ATTR_ERRMODE,
        PDO::ERRMODE_EXCEPTION
    );

    $conexion->setAttribute(
        PDO::ATTR_DEFAULT_FETCH_MODE,
        PDO::FETCH_ASSOC
    );
} catch (PDOException $e) {
    error_log($e->getMessage());
    exit('No se pudo conectar con la base de datos.');
}
