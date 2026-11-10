<?php
// Asegurar reporte de errores para depuración
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/recepcionista_auth.php';
requerir_recepcionista('../view/recepcionista/lista_recepcionista.php');

// Incluir la conexión global de la base de datos
require_once __DIR__ . '/../config/database.php';

class LogicaPaciente {
    private $conn;

    public function __construct() {
        global $conexion;
        $this->conn = $conexion;
    }

    // Método para registrar un nuevo paciente
    public function registrarPaciente($nombre, $telefono, $email, $fecha_nac, $alergias) {
        try {
            $sql = "INSERT INTO paciente (nombre, telefono, email, fecha_nac, alergias) 
                    VALUES (:nombre, :telefono, :email, :fecha_nac, :alergias)";
            
            $stmt = $this->conn->prepare($sql);
            
            return $stmt->execute([
                ':nombre'    => trim($nombre),
                ':telefono'  => trim($telefono),
                ':email'     => !empty($email) ? trim($email) : null,
                ':fecha_nac' => !empty($fecha_nac) ? $fecha_nac : null,
                ':alergias'  => !empty($alergias) ? trim($alergias) : null
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }
}

// Procesar el formulario cuando se envíe por POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre    = $_POST['nombre'] ?? '';
    $telefono  = $_POST['telefono'] ?? '';
    $email     = $_POST['email'] ?? '';
    $fecha_nac = $_POST['fecha_nac'] ?? '';
    $alergias  = $_POST['alergias'] ?? '';

    if (!empty($nombre) && !empty($telefono)) {
        $logica = new LogicaPaciente();
        $resultado = $logica->registrarPaciente($nombre, $telefono, $email, $fecha_nac, $alergias);

        if ($resultado) {
            header("Location: ../view/paciente/lista_pacientes.php?registro=exito");
            exit();
        } else {
            $error = "Hubo un error al registrar el paciente en la base de datos.";
        }
    } else {
        $error = "Por favor completa los campos obligatorios (Nombre y Teléfono).";
    }
}
?>