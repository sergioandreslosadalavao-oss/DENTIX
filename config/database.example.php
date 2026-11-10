<?php
$host = '127.0.0.1';
$port = '5433';
$dbname = 'citas_odontologicas';
$user = 'tu_usuario';
$password = 'tu_contraseña';

try {
    $conexion = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $user, $password);
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conexion->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    exit('Error de conexión');
}
?>