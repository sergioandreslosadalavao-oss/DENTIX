<?php
// Registro de cuentas de recepción, disponible solo para personal autenticado.
require_once __DIR__ . '/../../app/recepcionista_auth.php';
requerir_recepcionista('lista_recepcionista.php');
require_once "../../config/database.php";

$error = $_SESSION['error'] ?? "";
unset($_SESSION['error']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($nombre !== '' && $telefono !== '' && filter_var($email, FILTER_VALIDATE_EMAIL) && $password !== '') {
        try {
            $stmt_check = $conexion->prepare("SELECT id FROM recepcionista WHERE email = ?");
            $stmt_check->execute([$email]);
            
            if ($stmt_check->fetch()) {
                $error = "El correo ya se encuentra registrado.";
            } else {
                $password_hashed = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conexion->prepare(
                    "INSERT INTO recepcionista (nombre, telefono, email, password_hash)
                     VALUES (?, ?, ?, ?) RETURNING id"
                );
                $stmt->execute([$nombre, $telefono, $email, $password_hashed]);
                $stmt->fetchColumn();

                $_SESSION['mensaje'] = "La cuenta de recepción fue creada.";
                header("Location: ../cita/lista_citas.php");
                exit();
            }
        } catch (PDOException $e) {
            error_log("No se pudo crear la cuenta de recepción: " . $e->getMessage());
            $error = "No se pudo crear la cuenta. Verifica los datos e inténtalo de nuevo.";
        }
    } else {
        $error = "Completa todos los campos con datos válidos.";
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
                        <input type="text" class="form-control border-start-0" id="nombre" name="nombre" maxlength="100" placeholder="Ej. José Antonio" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="telefono" class="form-label small fw-semibold text-dark">Teléfono</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-phone text-muted"></i></span>
                        <input type="tel" class="form-control border-start-0" id="telefono" name="telefono" maxlength="20" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label small fw-semibold text-dark">Correo Electrónico</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-envelope text-muted"></i></span>
                        <input type="email" class="form-control border-start-0" id="email" name="email" maxlength="100" placeholder="jose.antonio@gmail.com" required>
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
                    <a href="lista_recepcionista.php" class="text-primary fw-bold text-decoration-none">
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
    <script src="../../assests/js/dentix-i18n.js?v=20261110-cur"></script>
</body>
</html>