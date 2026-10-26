<?php
$host = '127.0.0.1';
$port = '5433'; // <-- Tu puerto local activo de PostgreSQL
$dbname = 'citas_odontologicas'; // Nombre de tu base de datos
$user = 'postgres'; // Tu usuario
$password = '12141214'; // Tu contraseña real de pgAdmin

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
    // Reemplaza temporalmente el exit genérico por esto para ver errores exactos si ocurren:
    exit('Error exacto de PostgreSQL: ' . $e->getMessage());
}
?>
