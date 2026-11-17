<?php

$host = getenv('DB_HOST') ?: '127.0.0.1';
$port = getenv('DB_PORT') ?: '5432';
$dbname = getenv('DB_NAME') ?: 'citas_odontologicas';
$user = getenv('DB_USER') ?: 'TU_USUARIO_POSTGRES';
$password = getenv('DB_PASSWORD') ?: 'TU_CONTRASENA';

try {
    $conexion = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $user, $password);
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conexion->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log('Error de conexión a PostgreSQL: ' . $e->getMessage());
    http_response_code(500);
    exit('No se pudo conectar con la base de datos. Revisa config/database.php.');
}

function db(): PDO
{
    global $conexion;

    return $conexion;
}

