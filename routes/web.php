<?php

return [
    'inicio' => ['home_controller.php', 'home_index'],

    'login-paciente' => ['paciente_controller.php', 'paciente_login'],
    'registro-paciente' => ['paciente_controller.php', 'paciente_registro'],
    'portal-paciente' => ['paciente_controller.php', 'paciente_portal'],
    'perfil-paciente' => ['paciente_controller.php', 'paciente_perfil'],
    'agendar-cita' => ['paciente_controller.php', 'paciente_agendar'],
    'logout-paciente' => ['paciente_controller.php', 'paciente_logout'],
    'pacientes' => ['paciente_controller.php', 'paciente_index'],
    'pacientes/editar' => ['paciente_controller.php', 'paciente_editar'],
    'pacientes/procesar' => ['paciente_controller.php', 'paciente_procesar'],

    'login-odontologo' => ['odontologo_controller.php', 'odontologo_login'],
    'panel-odontologo' => ['odontologo_controller.php', 'odontologo_panel'],
    'historial-odontologo' => ['odontologo_controller.php', 'odontologo_historial'],
    'logout-odontologo' => ['odontologo_controller.php', 'odontologo_logout'],
    'odontologos' => ['odontologo_controller.php', 'odontologo_index'],
    'odontologos/nuevo' => ['odontologo_controller.php', 'odontologo_crear'],
    'odontologos/editar' => ['odontologo_controller.php', 'odontologo_editar'],
    'odontologos/procesar' => ['odontologo_controller.php', 'odontologo_procesar'],

    'login-recepcionista' => ['recepcionista_controller.php', 'recepcionista_login'],
    'registro-recepcionista' => ['recepcionista_controller.php', 'recepcionista_registro'],
    'logout-recepcionista' => ['recepcionista_controller.php', 'recepcionista_logout'],

    'citas' => ['cita_controller.php', 'cita_index'],
    'citas/nueva' => ['cita_controller.php', 'cita_crear'],
    'citas/editar' => ['cita_controller.php', 'cita_editar'],
    'citas/procesar' => ['cita_controller.php', 'cita_procesar'],

    'servicios' => ['servicio_controller.php', 'servicio_index'],
    'servicios/nuevo' => ['servicio_controller.php', 'servicio_crear'],
    'servicios/editar' => ['servicio_controller.php', 'servicio_editar'],
    'servicios/procesar' => ['servicio_controller.php', 'servicio_procesar'],
    'tratamientos' => ['servicio_controller.php', 'servicio_catalogo'],

    'reportes' => ['reporte_controller.php', 'reporte_index'],
];
