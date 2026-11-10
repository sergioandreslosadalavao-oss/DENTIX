<?php
$host = '127.0.0.1';
$port = '5432';
$dbname = 'citas_odontologicas';
$user = 'postgres';
$password = '1214121400';

try {
    $conexion = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $user, $password);
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conexion->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    exit('Error de conexión');
}
