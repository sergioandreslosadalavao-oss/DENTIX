<?php
// view/odontologo/cerrar_sesion_odontologo.php — Cierre de sesión del odontólogo
require_once __DIR__ . "/../../app/sesion.php";

session_unset();
session_destroy();

header('Location: login_odontologo.php');
exit();
