<?php
// index.php - Dashboard Principal
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SCO - Sistema de Citas Odontológicas</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome para Iconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        body { background-color: #f4f7f6; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .hero-banner { background: linear-gradient(135deg, #0d6efd 0%, #004085 100%); color: white; border-radius: 15px; padding: 2.5rem; margin-bottom: 2rem; }
        .card-custom { border: none; border-radius: 12px; transition: transform 0.2s ease, box-shadow 0.2s ease; }
        .card-custom:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important; }
        .icon-box { font-size: 2.5rem; margin-bottom: 1rem; color: #0d6efd; }
    </style>
</head>
<body>

    <div class="container my-5">
        <!-- Banner Principal -->
        <div class="hero-banner shadow text-center">
            <h1 class="display-5 fw-bold"><i class="fa-solid font-awesome fa-tooth me-2"></i>Sistema de Citas Odontológicas</h1>
            <p class="lead mb-0">Gestión integral de pacientes, profesionales y servicios odontológicos</p>
        </div>

        <!-- Tarjetas de Navegación -->
        <div class="row g-4">
            <!-- Módulo Pacientes -->
            <div class="col-md-4">
                <div class="card card-custom shadow-sm h-100 p-4 text-center">
                    <div class="card-body">
                        <div class="icon-box"><i class="fa-solid fa-hospital-user"></i></div>
                        <h4 class="card-title fw-bold text-dark">Pacientes</h4>
                        <p class="card-text text-muted">Administra el historial de pacientes y datos generales.</p>
                        <div class="d-grid gap-2 mt-4">
                            <a href="view/paciente/lista_pacientes.php" class="btn btn-outline-primary btn-lg"><i class="fa-solid fa-list me-2"></i>Ver Pacientes</a>
                            <a href="view/paciente/form_paciente.php" class="btn btn-primary btn-lg"><i class="fa-solid fa-user-plus me-2"></i>Nuevo Paciente</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Módulo Odontólogos -->
            <div class="col-md-4">
                <div class="card card-custom shadow-sm h-100 p-4 text-center">
                    <div class="card-body">
                        <div class="icon-box"><i class="fa-solid fa-user-doctor"></i></div>
                        <h4 class="card-title fw-bold text-dark">Odontólogos</h4>
                        <p class="card-text text-muted">Gestión de especialistas y disponibilidad del cuerpo médico.</p>
                        <div class="d-grid gap-2 mt-4">
                            <a href="view/odontologo/lista_odontologos.php" class="btn btn-outline-primary btn-lg"><i class="fa-solid fa-users-gear me-2"></i>Ver Especialistas</a>
                            <a href="view/odontologo/form_odontologo.php" class="btn btn-primary btn-lg"><i class="fa-solid fa-user-plus me-2"></i>Nuevo Odontólogo</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Módulo Servicios -->
            <div class="col-md-4">
                <div class="card card-custom shadow-sm h-100 p-4 text-center">
                    <div class="card-body">
                        <div class="icon-box"><i class="fa-solid fa-notes-medical"></i></div>
                        <h4 class="card-title fw-bold text-dark">Servicios</h4>
                        <p class="card-text text-muted">Catálogo de tratamientos odontológicos disponibles y tarifas.</p>
                        <div class="d-grid gap-2 mt-4">
                            <a href="view/servicio/lista_servicios.php" class="btn btn-outline-primary btn-lg"><i class="fa-solid fa-list-check me-2"></i>Ver Servicios</a>
                            <a href="view/servicio/form_servicio.php" class="btn btn-primary btn-lg"><i class="fa-solid fa-plus-circle me-2"></i>Nuevo Servicio</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Fila Inferior para Gestión de Citas y Agenda -->
        <div class="row g-4 mt-2">
            <div class="col-12">
                <div class="card card-custom shadow-sm p-4 text-center bg-white">
                    <div class="card-body">
                        <div class="icon-box text-success mb-2"><i class="fa-solid fa-calendar-days"></i></div>
                        <h4 class="card-title fw-bold text-dark">Módulo de Citas y Agenda</h4>
                        <p class="card-text text-muted">Control general de programación, asignación de odontólogos activos y turnos.</p>
                        <div class="d-flex justify-content-center gap-3 mt-4">
                            <a href="view/cita/lista_citas.php" class="btn btn-outline-success btn-lg px-4"><i class="fa-solid fa-calendar-days me-2"></i>Ver Agenda / Citas</a>
                            <a href="view/cita/form_cita.php" class="btn btn-success btn-lg px-4"><i class="fa-solid fa-calendar-plus me-2"></i>Agendar Nueva Cita</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>