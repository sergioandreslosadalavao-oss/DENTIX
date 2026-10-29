<?php
// form_recepcionista.php - Registro de recepcionista con redirección forzada al panel principal de admin
require_once "../../config/database.php";
session_start();

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $correo = trim($_POST['correo'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (!empty($nombre) && !empty($correo) && !empty($password)) {
        try {
            // Verificar si el correo ya existe
            $stmt_check = $conexion->prepare("SELECT id FROM recepcionista WHERE correo = ?");
            $stmt_check->execute([$correo]);
            
            if ($stmt_check->fetch()) {
                $error = "El correo ya se encuentra registrado.";
            } else {
                // Encriptar contraseña y registrar
                $password_hashed = password_hash($password, PASSWORD_BCRYPT);
                $stmt = $conexion->prepare("INSERT INTO recepcionista (nombre, correo, password) VALUES (?, ?, ?)");
                $stmt->execute([$nombre, $correo, $password_hashed]);
                
                $nuevo_id = $conexion->lastInsertId();

                // Autologin e iniciar sesión de una vez
                $_SESSION['recepcionista_id'] = $nuevo_id;
                $_SESSION['recepcionista_nombre'] = $nombre;
                $_SESSION['recepcionista_correo'] = $correo;

                // REDIRECCIÓN FORZADA AL PANEL DE ADMINISTRACIÓN PRINCIPAL (index.php de la raíz)
                header("Location: ../../index.php");
                exit();
            }
        } catch (PDOException $e) {
            $error = "Error al registrar la cuenta: " . $e->getMessage();
        }
    } else {
        $error = "Por favor, completa todos los campos del formulario.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro Recepción - DENTIX</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome para Iconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    
    <!-- Hojas de estilos -->
    <link rel="stylesheet" href="../../assests/css/admin.css">
    <link rel="stylesheet" href="../../assests/css/index.css">
</head>
<body class="bg-light" style="display: grid; place-items: center; min-height: 100vh; margin: 0;">

    <div class="container" style="max-width: 440px;">
        <div class="card border-0 shadow-lg rounded-4 p-4 p-md-5 bg-white">
            
            <!-- Encabezado -->
            <div class="text-center mb-4">
                <div class="d-inline-flex align-items-center justify-content-center bg-teal text-white rounded-circle mb-3" style="width: 56px; height: 56px; background-color: #0d9488;">
                    <i class="fa-solid fa-user-plus fs-4"></i>
                </div>
                <h3 class="fw-bold text-dark mb-1">Registro Recepción</h3>
                <p class="text-muted small">Crea una cuenta para administrar el panel de DENTIX</p>
            </div>

            <!-- Alerta de error -->
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger py-2 small mb-4 shadow-sm border-0 d-flex align-items-center">
                    <i class="fa-solid fa-triangle-exclamation me-2"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <!-- Formulario de Registro -->
            <form action="form_recepcionista.php" method="POST">
                <div class="mb-3">
                    <label for="nombre" class="form-label small fw-semibold text-dark">Nombre Completo</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-user text-muted"></i></span>
                        <input type="text" class="form-control border-start-0" id="nombre" name="nombre" placeholder="Ej. José Antonio" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="correo" class="form-label small fw-semibold text-dark">Correo Electrónico</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-envelope text-muted"></i></span>
                        <input type="email" class="form-control border-start-0" id="correo" name="correo" placeholder="jose.antonio@gmail.com" required>
                    </div>
                </div>

                <div class="mb-4">
                    <label for="password" class="form-label small fw-semibold text-dark">Contraseña</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-lock text-muted"></i></span>
                        <input type="password" class="form-control border-start-0" id="password" name="password" placeholder="••••••••" required>
                    </div>
                </div>

                <button type="submit" class="btn text-white w-100 py-2 fw-bold rounded-pill mb-4" style="background-color: #0d9488; border: none;">
                    <i class="fa-solid fa-check me-2"></i> Crear Cuenta y Entrar
                </button>

                <div class="d-flex justify-content-between align-items-center pt-3 border-top small">
                    <a href="login_recepcionista.php" class="text-primary fw-bold text-decoration-none">
                        <i class="fa-solid fa-right-to-bracket me-1"></i> Ya tengo cuenta
                    </a>
                    <a href="../../index.php" class="text-muted text-decoration-none">
                        <i class="fa-solid fa-arrow-left me-1"></i> Ir al Inicio
                    </a>
                </div>
            </form>

        </div>
    </div>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>