<?php
// login_recepcionista.php - Autenticación de Recepcionista
require_once "../../config/database.php";
session_start();

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (!empty($email) && !empty($password)) {
        try {
            // Buscar al recepcionista en la base de datos por correo
            $stmt = $conexion->prepare("SELECT * FROM recepcionista WHERE email = ?");
            $stmt->execute([$email]);
            $recepcionista = $stmt->fetch();

            if ($recepcionista) {
                // Verificar la contraseña con password_verify (compatible con los hash de tu base de datos)
                if (password_verify($password, $recepcionista['password_hash'])) {
                    $_SESSION['recepcionista_id'] = $recepcionista['id'];
                    $_SESSION['recepcionista_nombre'] = $recepcionista['nombre'];
                    
                    // Redirigir directamente al panel de citas o gestión
                    header("Location: ../cita/lista_citas.php");
                    exit;
                } else {
                    $error = "Contraseña incorrecta.";
                }
            } else {
                $error = "No se encontró ninguna recepcionista con ese correo.";
            }
        } catch (PDOException $e) {
            $error = "Error de conexión con la base de datos.";
        }
    } else {
        $error = "Por favor complete todos los campos.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Recepción DENTIX</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@500;600;700;800&family=Sora:wght@700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    
    <link rel="stylesheet" href="../../assests/css/admin.css">
    <link rel="stylesheet" href="../../assests/css/index.css">
</head>
<body class="admin-body" style="display: grid; place-items: center; min-height: 100vh;">

    <div class="container" style="max-width: 460px;">
        <div class="form-card shadow-lg p-4">
            <div class="text-center mb-4">
                <div class="brand__mark mx-auto mb-3" style="width: 50px; height: 50px; display: grid; place-items: center; background: linear-gradient(140deg, #3fd9c5, #10a294); border-radius: 14px; color: white; font-size: 1.2rem;">
                    <i class="fa-solid fa-user-shield"></i>
                </div>
                <h2 class="fw-bold">Portal Administrador</h2>
                <p class="text-muted small">Ingresa tus credenciales de recepcionista</p>
            </div>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger py-2 mb-3 small" role="alert">
                    <i class="fa-solid fa-triangle-exclamation me-1"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form action="login_recepcionista.php" method="POST">
                <div class="mb-3">
                    <label for="email" class="form-label fw-semibold small">Correo Electrónico</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="fa-solid fa-envelope text-muted"></i></span>
                        <input type="email" class="form-control" id="email" name="email" required placeholder="maria.recepcion@clinica.com">
                    </div>
                </div>

                <div class="mb-4">
                    <label for="password" class="form-label fw-semibold small">Contraseña</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="fa-solid fa-lock text-muted"></i></span>
                        <input type="password" class="form-control" id="password" name="password" required placeholder="••••••••">
                    </div>
                </div>

                <div class="d-grid gap-2 mb-3">
                    <button type="submit" class="btn btn--primary py-2">
                        <i class="fa-solid fa-right-to-bracket me-2"></i> Iniciar Sesión
                    </button>
                </div>

                <div class="text-center">
                    <a href="../../index.php" class="text-muted small text-decoration-none">
                        <i class="fa-solid fa-arrow-left me-1"></i> Volver al Inicio
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>