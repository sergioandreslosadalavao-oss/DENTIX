<?php
// form_recepcionista.php - Registro de Nueva Recepcionista con diseño unificado
require_once "../../config/database.php";

$mensaje = "";
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (!empty($nombre) && !empty($telefono) && !empty($email) && !empty($password)) {
        try {
            // Verificar si el correo ya está registrado
            $check = $conexion->prepare("SELECT id FROM recepcionista WHERE email = ?");
            $check->execute([$email]);
            
            if ($check->rowCount() > 0) {
                $error = "El correo electrónico ya se encuentra registrado en el sistema.";
            } else {
                // Cifrar la contraseña de forma segura con password_hash (bcrypt)
                $password_hash = password_hash($password, PASSWORD_DEFAULT);

                $stmt = $conexion->prepare("INSERT INTO recepcionista (nombre, telefono, email, password_hash) VALUES (?, ?, ?, ?)");
                $stmt->execute([$nombre, $telefono, $email, $password_hash]);

                $mensaje = "¡Recepcionista registrada con éxito! Ya puedes iniciar sesión.";
            }
        } catch (PDOException $e) {
            $error = "Error al guardar en la base de datos: " . $e->getMessage();
        }
    } else {
        $error = "Por favor, completa todos los campos obligatorios.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Nueva Recepcionista - DENTIX</title>
    
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
<body class="admin-body" style="display: grid; place-items: center; min-height: 100vh; padding: 24px 0;">

    <div class="container" style="max-width: 520px;">
        <div class="form-card shadow-lg p-4 p-md-5 border-0 rounded-4">
            
            <!-- Encabezado -->
            <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
                <div>
                    <span class="eyebrow mb-2">Nuevo Usuario</span>
                    <h2 class="fw-bold text-dark mb-0 fs-3">Registrar Recepcionista</h2>
                </div>
                <a href="lista_recepcionista.php" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                    <i class="fa-solid fa-arrow-left me-1"></i> Volver
                </a>
            </div>

            <!-- Alertas de éxito o error -->
            <?php if (!empty($mensaje)): ?>
                <div class="alert alert-success py-2 mb-4 small d-flex align-items-center" role="alert">
                    <i class="fa-solid fa-circle-check me-2 fs-5"></i> 
                    <div>
                        <?php echo $mensaje; ?>
                        <div class="mt-1"><a href="lista_recepcionista.php" class="fw-bold text-success text-decoration-underline">Ir al login</a></div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger py-2 mb-4 small d-flex align-items-center" role="alert">
                    <i class="fa-solid fa-triangle-exclamation me-2 fs-5"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <!-- Formulario de Registro -->
            <form action="form_recepcionista.php" method="POST">
                <div class="mb-3">
                    <label for="nombre" class="form-label fw-semibold small">Nombre Completo</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-user text-muted"></i></span>
                        <input type="text" class="form-control border-start-0" id="nombre" name="nombre" required placeholder="Ej. Ana María Gómez">
                    </div>
                </div>

                <div class="mb-3">
                    <label for="telefono" class="form-label fw-semibold small">Teléfono / Celular</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-phone text-muted"></i></span>
                        <input type="text" class="form-control border-start-0" id="telefono" name="telefono" required placeholder="Ej. 3001234567">
                    </div>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label fw-semibold small">Correo Electrónico (Usuario)</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-envelope text-muted"></i></span>
                        <input type="email" class="form-control border-start-0" id="email" name="email" required placeholder="ana.recepcion@clinica.com">
                    </div>
                </div>

                <div class="mb-4">
                    <label for="password" class="form-label fw-semibold small">Contraseña de Acceso</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-lock text-muted"></i></span>
                        <input type="password" class="form-control border-start-0" id="password" name="password" required placeholder="••••••••">
                    </div>
                    <div class="form-text text-muted mt-1" style="font-size: 0.72rem;">Esta contraseña se cifrará de forma segura para proteger el sistema.</div>
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn--primary py-2 fw-bold">
                        <i class="fa-solid fa-floppy-disk me-2"></i> Guardar Recepcionista
                    </button>
                </div>
            </form>

        </div>
    </div>

    <!-- Bootstrap JS y Librerías -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
    <script src="../../assests/js/admin-animations.js"></script>
</body>
</html>