<?php
// view/paciente/editar_paciente.php - Acceso restringido para Recepción (RF-037 / RF-042 / Ley 1581)
// La actualización de datos personales y clínicos corresponde exclusivamente al paciente titular desde su Portal.
require_once __DIR__ . '/../../app/recepcionista_auth.php';
requerir_recepcionista('../recepcionista/lista_recepcionista.php');

$_SESSION['mensaje'] = 'Recepción no tiene permisos para editar expedientes de pacientes. Cada paciente actualiza sus propios datos desde su Portal (Ley 1581 / RF-042).';
$_SESSION['tipo_mensaje'] = 'error';
header('Location: lista_pacientes.php');
exit;
