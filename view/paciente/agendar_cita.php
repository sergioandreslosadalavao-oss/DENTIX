<?php
require_once __DIR__ . '/../../app/paciente_auth.php';
iniciar_sesion_paciente();

if (!paciente_autenticado()) {
    header('Location: login_paciente.php?next=portal_paciente.php');
    exit;
}

header('Location: portal_paciente.php');
exit;
