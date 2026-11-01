<?php
// view/odontologo/login_odontologo.php — Acceso del odontólogo (RN-07: solo líder)
require_once "../../config/database.php";
session_start();

// Si ya hay sesión de líder, va directo al panel de la especialidad
if (isset($_SESSION['odontologo_id'])) {
    header("Location: lista_odontologos.php");
    exit();
}

$error = $_SESSION['error'] ?? '';
unset($_SESSION['error']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso Odontólogo - DENTIX</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body class="bg-light" style="display: grid; place-items: center; min-height: 100vh; margin: 0;">

    <div class="container" style="max-width: 440px;">
        <div class="card border-0 shadow-lg rounded-4 p-4 p-md-5 bg-white">

            <div class="text-center mb-4">
                <div class="d-inline-flex align-items-center justify-content-center text-white rounded-circle mb-3" style="width: 56px; height: 56px; background-color: #0369a1;">
                    <i class="fa-solid fa-user-doctor fs-4"></i>
                </div>
                <h3 class="fw-bold text-dark mb-1">Acceso Odontólogo</h3>
                <p class="text-muted small">La gestión de odontólogos la realiza el odontólogo líder (RN-07)</p>
            </div>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger py-2 small mb-4 shadow-sm border-0 d-flex align-items-center">
                    <i class="fa-solid fa-triangle-exclamation me-2"></i> <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
                </div>
            <?php endif; ?>

            <form action="../../app/logica_odontologo.php" method="POST">
                <input type="hidden" name="accion" value="login_odontologo">

                <div class="mb-3">
                    <label for="email" class="form-label small fw-semibold text-dark">Correo Electrónico</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-envelope text-muted"></i></span>
                        <input type="email" class="form-control border-start-0" id="email" name="email" placeholder="ana.torres@clinica.com" autocomplete="username" required>
                    </div>
                </div>

                <div class="mb-4">
                    <label for="password" class="form-label small fw-semibold text-dark">Contraseña</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-lock text-muted"></i></span>
                        <input type="password" class="form-control border-start-0" id="password" name="password" placeholder="••••••••" autocomplete="current-password" required>
                    </div>
                </div>

                <button type="submit" class="btn text-white w-100 py-2 fw-bold rounded-pill mb-4" style="background-color: #0369a1; border: none;">
                    <i class="fa-solid fa-right-to-bracket me-2"></i> Iniciar Sesión
                </button>

                <div class="d-flex justify-content-between align-items-center pt-3 border-top small">
                    <a href="../recepcionista/login_recepcionista.php" class="text-muted text-decoration-none">
                        <i class="fa-solid fa-user-shield me-1"></i> Acceso Recepción
                    </a>
                    <a href="../../index.php" class="text-muted text-decoration-none">
                        <i class="fa-solid fa-arrow-left me-1"></i> Ir al Inicio
                    </a>
                </div>
            </form>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
