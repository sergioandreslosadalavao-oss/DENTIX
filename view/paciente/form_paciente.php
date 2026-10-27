<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Paciente - SCO</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="card shadow-sm border-0 rounded-3">
                    <div class="card-header bg-primary text-white p-3">
                        <h4 class="card-title mb-0 text-center">Registrar Paciente</h4>
                    </div>
                    <div class="card-body p-4">
                        <form action="../../app/logica_paciente.php" method="POST">
                            
                            <div class="mb-3">
                                <label for="nombre" class="form-label fw-bold">Nombre Completo *</label>
                                <input type="text" class="form-control" id="nombre" name="nombre" placeholder="Ej. Juan Pérez" required>
                            </div>

                            <div class="mb-3">
                                <label for="telefono" class="form-label fw-bold">Teléfono *</label>
                                <input type="tel" class="form-control" id="telefono" name="telefono" placeholder="Ej. 3001234567" required>
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label fw-bold">Correo Electrónico</label>
                                <input type="email" class="form-control" id="email" name="email" placeholder="ejemplo@correo.com">
                            </div>

                            <div class="mb-3">
                                <label for="fecha_nac" class="form-label fw-bold">Fecha de Nacimiento</label>
                                <input type="date" class="form-control" id="fecha_nac" name="fecha_nac">
                            </div>

                            <div class="mb-3">
                                <label for="alergias" class="form-label fw-bold">Alergias / Observaciones</label>
                                <textarea class="form-control" id="alergias" name="alergias" rows="3" placeholder="Escriba aquí si el paciente presenta alergias"></textarea>
                            </div>

                            <div class="d-grid gap-2 d-md-flex justify-content-md-between mt-4">
                                <a href="lista_pacientes.php" class="btn btn-outline-secondary">
                                    Volver a la Lista
                                </a>
                                <button type="submit" class="btn btn-primary px-4">
                                    Registrar Paciente
                                </button>
                            </div>

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>