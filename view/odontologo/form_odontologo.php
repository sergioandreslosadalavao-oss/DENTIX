<?php
// view/odontologo/form_odontologo.php — Registro de Odontólogo (RN-07: solo líder)
// La lógica vive en app/logica_odontologo.php; aquí solo está la vista y la guarda.

require_once "../../config/database.php";
require_once "../../app/sesion.php";
session_start();

// Registro de odontólogos: SOLO el líder activo entra a esta pantalla (RN-07)
sesion_requiere_odontologo_lider("../recepcionista/login_recepcionista.php");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Odontólogo - SCO</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@500;600;700;800&family=Sora:wght@700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="../../assests/css/admin.css">
    <link rel="stylesheet" href="../../assests/css/index.css">
</head>
<body class="bg-light" style="display: grid; place-items: center; min-height: 100vh; margin: 0; font-family: 'Manrope', sans-serif;">

    <div class="container" style="max-width: 540px; padding: 20px;">
        <div class="card border-0 shadow-lg rounded-4 p-4 p-md-5 bg-white">

            <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
                <div>
                    <span class="badge bg-teal bg-opacity-10 text-teal px-3 py-1 rounded-pill fw-bold mb-2" style="background-color: #ccfbf1; color: #0d9488; font-size: 0.75rem;">
                        <i class="fa-solid fa-circle-dot me-1"></i> ESPECIALISTAS
                    </span>
                    <h3 class="fw-bold text-dark m-0" style="font-family: 'Sora', sans-serif;">Registrar Odontólogo</h3>
                </div>
                <a href="lista_odontologos.php" class="text-muted text-decoration-none small fw-semibold">
                    <i class="fa-solid fa-arrow-left me-1"></i> Inicio
                </a>
            </div>

            <!-- La lógica y la guarda RN-07 viven en app/logica_odontologo.php -->
            <form action="../../app/logica_odontologo.php" method="POST">
                <input type="hidden" name="accion" value="registrar">

                <div class="mb-3">
                    <label for="nombre" class="form-label small fw-semibold text-dark">Nombre Completo</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-user-doctor text-muted"></i></span>
                        <input type="text" class="form-control border-start-0" id="nombre" name="nombre" placeholder="Ej. Dr. Carlos Pérez" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="especialidad" class="form-label small fw-semibold text-dark">Especialidad</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-stethoscope text-muted"></i></span>
                        <input type="text" class="form-control border-start-0" id="especialidad" name="especialidad" placeholder="Ej. Ortodoncia / Endodoncia" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="consultorio" class="form-label small fw-semibold text-dark">Consultorio</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-door-open text-muted"></i></span>
                        <input type="text" class="form-control border-start-0" id="consultorio" name="consultorio" placeholder="Ej. Consultorio 302">
                    </div>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label small fw-semibold text-dark">Correo Electrónico (Login)</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-envelope text-muted"></i></span>
                        <input type="email" class="form-control border-start-0" id="email" name="email" placeholder="carlos.perez@clinica.com" required>
                    </div>
                </div>

                <div class="mb-4">
                    <label for="password" class="form-label small fw-semibold text-dark">Contraseña de Acceso (mín. 8 caracteres)</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-lock text-muted"></i></span>
                        <input type="password" class="form-control border-start-0" id="password" name="password" placeholder="••••••••" minlength="8" required>
                    </div>
                </div>

                <button type="submit" class="btn text-white w-100 py-2 fw-bold rounded-pill mb-4 shadow-sm" style="background-color: #0d9488; border: none;">
                    <i class="fa-solid fa-user-plus me-2"></i> Registrar Odontólogo Asociado
                </button>

                <div class="text-center pt-3 border-top small">
                    <a href="lista_odontologos.php" class="text-primary fw-bold text-decoration-none d-inline-flex align-items-center gap-1 mb-2">
                        <i class="fa-solid fa-list"></i> Ver odontólogos registrados
                    </a>
                    <br>
                    <a href="login_odontologo.php" class="text-muted text-decoration-none">
                        <i class="fa-solid fa-right-to-bracket me-1"></i> Acceso odontólogo
                    </a>
                </div>
            </form>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
