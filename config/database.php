
<?php

$host = '127.0.0.1';
$port = '5432';
$dbname = 'sco';
$user = 'sergio';
$password = '162025Checho';

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
