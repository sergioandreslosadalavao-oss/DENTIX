<?php
require_once __DIR__ . '/recepcionista_auth.php';
requerir_recepcionista('../view/recepcionista/lista_recepcionista.php');
require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $paciente_id = $_POST['paciente_id'];
    $odontologo_id = $_POST['odontologo_id'];
    $servicio_id = $_POST['servicio_id'];
    $fecha = $_POST['fecha'];
    $hora = $_POST['hora'];
    $motivo = $_POST['motivo'];

    try {
        $sql = "INSERT INTO cita (paciente_id, odontologo_id, servicio_id, fecha, hora, motivo, estado) 
                VALUES (:paciente_id, :odontologo_id, :servicio_id, :fecha, :hora, :motivo, 'pendiente')";
        
        $stmt = $conexion->prepare($sql);
        $stmt->execute([
            ':paciente_id' => $paciente_id,
            ':odontologo_id' => $odontologo_id,
            ':servicio_id' => $servicio_id,
            ':fecha' => $fecha,
            ':hora' => $hora,
            ':motivo' => $motivo
        ]);

        $_SESSION['mensaje'] = "¡Cita agendada exitosamente!";
        $_SESSION['tipo_mensaje'] = "exito";
    } catch (PDOException $e) {
        $_SESSION['mensaje'] = "Error al agendar la cita: " . $e->getMessage();
        $_SESSION['tipo_mensaje'] = "error";
    }

    header("Location: ../view/paciente/lista_pacientes.php");
    exit();
}
?>