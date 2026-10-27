<?php
// Incluimos directamente el archivo de conexión que ya configura el PDO
require_once(__DIR__ . '/../config/database.php');

class LogicaRecepcionista {
    private $conn;

    public function __construct() {
        global $conexion; // Heredamos la variable global $conexion definida en database.php
        $this->conn = $conexion;
    }

    // Obtener solo los odontólogos que están activos para las citas
    public function obtenerOdontologosActivos() {
        try {
            $sql = "SELECT id_odontologo, nombre, apellido, especialidad 
                    FROM odontologo 
                    WHERE estado = 'activo' OR activo = 1 OR estado IS NULL";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    // Registrar una cita asignada por la recepcionista
    public function registrarCitaPorRecepcionista($id_paciente, $id_odontologo, $id_servicio, $fecha_cita, $hora_cita) {
        try {
            $sql = "INSERT INTO cita (id_paciente, id_odontologo, id_servicio, fecha_cita, hora_cita, estado) 
                    VALUES (:id_paciente, :id_odontologo, :id_servicio, :fecha_cita, :hora_cita, 'Pendiente')";
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute([
                ':id_paciente'   => $id_paciente,
                ':id_odontologo' => $id_odontologo,
                ':id_servicio'   => $id_servicio,
                ':fecha_cita'    => $fecha_cita,
                ':hora_cita'     => $hora_cita
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }
}
?>