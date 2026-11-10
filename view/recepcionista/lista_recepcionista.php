<?php
// lista_recepcionista.php - Portal de Inicio de Sesión y Registro de Recepcionista
require_once "../../config/database.php";
session_start();

if (isset($_SESSION['recepcionista_id'])) {
    header("Location: ../cita/lista_citas.php");
    exit;
}

$error = $_SESSION['error'] ?? "";
unset($_SESSION['error']);

// Manejo del Login de Recepcionista
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion_login'])) {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (!empty($email) && !empty($password)) {
        try {
            $stmt = $conexion->prepare("SELECT id, nombre, password_hash FROM recepcionista WHERE email = ? AND activo = TRUE");
            $stmt->execute([$email]);
            $recepcionista = $stmt->fetch();

            if ($recepcionista && password_verify($password, $recepcionista['password_hash'])) {
                session_regenerate_id(true);
                $_SESSION['recepcionista_id'] = $recepcionista['id'];
                $_SESSION['recepcionista_nombre'] = $recepcionista['nombre'];
                
                // Redirigir a la agenda de citas al autenticarse con éxito
                header("Location: ../cita/lista_citas.php");
                exit;
            } else {
                $error = "Credenciales incorrectas. Verifique correo y contraseña.";
            }
        } catch (PDOException $e) {
            $error = "Error al conectar con la base de datos.";
        }
    } else {
        $error = "Por favor diligencie todos los campos de inicio de sesión.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso Administrador - DENTIX</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@500;600;700;800&family=Sora:wght@700;800&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome para Iconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    
    <!-- ESTILOS GLOBALES DE LA CARPETA ASSESTS -->
    <link rel="stylesheet" href="../../assests/css/admin.css">
    <link rel="stylesheet" href="../../assests/css/index.css">
</head>
<body class="admin-body" style="display: grid; place-items: center; min-height: 100vh; padding: 20px 0;">

    <div class="container" style="max-width: 480px;">
        <div class="form-card shadow-lg p-4 border-0 rounded-4">
            
            <!-- Encabezado del Portal -->
            <div class="text-center mb-4">
                <div class="brand__mark mx-auto mb-3" style="width: 52px; height: 52px; display: grid; place-items: center; background: linear-gradient(140deg, #3fd9c5, #10a294); border-radius: 16px; color: white; font-size: 1.4rem;">
                    <i class="fa-solid fa-user-shield"></i>
                </div>
                <h2 class="fw-bold text-dark mb-1">Acceso Recepción</h2>
                <p class="text-muted small">Ingresa tus datos para gestionar la agenda de citas</p>
            </div>

            <!-- Mensajes de Estado -->
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger py-2 mb-3 small d-flex align-items-center" role="alert">
                    <i class="fa-solid fa-triangle-exclamation me-2 fs-6"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <!-- Formulario de Iniciar Sesión -->
            <form action="lista_recepcionista.php" method="POST">
                <input type="hidden" name="accion_login" value="1">

                <div class="mb-3">
                    <label for="email" class="form-label fw-semibold small">Correo Electrónico</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-envelope text-muted"></i></span>
                        <input type="email" class="form-control border-start-0" id="email" name="email" required placeholder="maria.recepcion@clinica.com">
                    </div>
                </div>

                <div class="mb-4">
                    <label for="password" class="form-label fw-semibold small">Contraseña</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-lock text-muted"></i></span>
                        <input type="password" class="form-control border-start-0" id="password" name="password" required placeholder="••••••••">
                    </div>
                </div>

                <div class="d-grid gap-2 mb-3">
                    <button type="submit" class="btn btn--primary py-2 fw-bold">
                        <i class="fa-solid fa-right-to-bracket me-2"></i> Iniciar Sesión
                    </button>
                </div>
            </form>

            <hr class="my-4 text-muted">

            <!-- Botones de Acción Alternativos -->
            <div class="d-flex justify-content-end align-items-center">
                <a href="../../index.php" class="text-secondary small text-decoration-none fw-semibold">
                    <i class="fa-solid fa-arrow-left me-1"></i> Ir al Inicio
                </a>
            </div>

        </div>
    </div>

    <!-- Bootstrap JS y Librerías -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
    <script src="../../assests/js/admin-animations.js"></script>
</body>
</html>