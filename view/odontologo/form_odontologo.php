<?php
// form_odontologo.php - Formulario para registrar nuevos odontólogos
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Odontólogo - SCO</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome para Iconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body class="bg-light">

    <div class="container my-5" style="max-width: 600px;">
        <div class="card border-0 shadow-lg rounded-4 p-4">
            <div class="card-body">
                <div class="text-center mb-4">
                    <h3 class="fw-bold text-primary"><i class="fa-solid fa-user-doctor me-2"></i>Registrar Odontólogo</h3>
                    <p class="text-muted">Ingrese la información del nuevo especialista para la clínica</p>
                </div>

                <!-- Formulario con la ruta lógica correspondiente -->
                <form action="../../app/logica_odontologo.php" method="POST">
                    
                    <!-- Nombre Completo -->
                    <div class="mb-3">
                        <label for="nombre" class="form-label fw-bold"><i class="fa-solid fa-user me-1"></i> Nombre Completo</label>
                        <input type="text" name="nombre" id="nombre" class="form-control" placeholder="Ej. Dr. Carlos Pérez" required>
                    </div>

                    <!-- Especialidad -->
                    <div class="mb-3">
                        <label for="especialidad" class="form-label fw-bold"><i class="fa-solid fa-stethoscope me-1"></i> Especialidad</label>
                        <input type="text" name="especialidad" id="especialidad" class="form-control" placeholder="Ej. Ortodoncia / Endodoncia" required>
                    </div>

                    <!-- Consultorio -->
                    <div class="mb-3">
                        <label for="consultorio" class="form-label fw-bold"><i class="fa-solid fa-door-open me-1"></i> Consultorio</label>
                        <input type="text" name="consultorio" id="consultorio" class="form-control" placeholder="Ej. Consultorio 302" required>
                    </div>

                    <!-- Botones de Acción -->
                    <div class="d-grid gap-2 mt-4">
                        <button type="submit" class="btn btn-success btn-lg">
                            <i class="fa-solid fa-user-plus me-2"></i>Registrar Odontólogo
                        </button>
                        <a href="lista_odontologos.php" class="btn btn-outline-primary">
                            <i class="fa-solid fa-list me-2"></i>Ver odontólogos registrados
                        </a>
                        <a href="../../index.php" class="btn btn-outline-secondary">
                            <i class="fa-solid fa-house me-2"></i>Volver al inicio
                        </a>
                    </div>

                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>