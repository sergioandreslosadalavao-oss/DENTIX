<?php
require_once __DIR__ . '/../../app/recepcionista_auth.php';
requerir_recepcionista('../recepcionista/lista_recepcionista.php');

// Si se accede directamente con ?id=... (sin pasar por logica_odontologo.php), cargamos el odontólogo aquí.
if (!isset($odontologo) || !is_array($odontologo)) {
    require_once __DIR__ . '/../../config/database.php';

    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    if ($id === false || $id === null || $id <= 0) {
        $_SESSION['mensaje'] = 'Error: el ID del odontólogo no es válido.';
        $_SESSION['tipo_mensaje'] = 'error';
        header('Location: lista_odontologos.php');
        exit;
    }

    try {
        $stmt = $conexion->prepare(
            'SELECT id, nombre, especialidad, consultorio, slug_publico, email, activo
             FROM odontologo
             WHERE id = :id'
        );
        $stmt->execute([':id' => $id]);
        $odontologo = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log('Error al consultar odontólogo en editar_odontologo.php: ' . $e->getMessage());
        $odontologo = false;
    }

    if (!$odontologo) {
        $_SESSION['mensaje'] = 'No se encontró el odontólogo solicitado.';
        $_SESSION['tipo_mensaje'] = 'error';
        header('Location: lista_odontologos.php');
        exit;
    }
}

$mensaje = $_SESSION['mensaje'] ?? null;
$tipo_mensaje = $_SESSION['tipo_mensaje'] ?? 'exito';
unset($_SESSION['mensaje'], $_SESSION['tipo_mensaje']);

$csrfToken = token_csrf_recepcionista();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Odontólogo - DENTIX</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@500;600;700;800&family=Sora:wght@700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="../../assests/css/admin.css">
    <link rel="stylesheet" href="../../assests/css/index.css">
</head>
<body class="admin-body" style="display: grid; place-items: center; min-height: 100vh; padding: 30px 15px;">

    <div class="container" style="max-width: 580px;">
        <div class="form-card shadow-lg p-4 p-md-5 border-0 rounded-4 bg-white">
            
            <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
                <div>
                    <span class="eyebrow mb-1 text-primary fw-bold" style="font-size: 0.75rem; letter-spacing: 0.05em; text-transform: uppercase;">Gestión de Especialistas</span>
                    <h2 class="fw-bold text-dark mb-0 fs-3" style="font-family: 'Sora', sans-serif;">
                        <i class="fa-solid fa-user-doctor text-primary me-2"></i> Editar Odontólogo
                    </h2>
                </div>
                <a href="lista_odontologos.php" class="btn btn-outline-secondary btn-sm rounded-pill px-3 fw-semibold">
                    <i class="fa-solid fa-arrow-left me-1"></i> Volver
                </a>
            </div>

            <?php if ($mensaje !== null): ?>
                <div class="alert <?= $tipo_mensaje === 'error' ? 'alert-danger' : 'alert-success' ?> py-2 mb-4 small d-flex align-items-center rounded-3" role="alert">
                    <i class="fa-solid <?= $tipo_mensaje === 'error' ? 'fa-triangle-exclamation' : 'fa-circle-check' ?> me-2 fs-5"></i>
                    <div><?= htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8') ?></div>
                </div>
            <?php endif; ?>

            <form action="../../app/logica_odontologo.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="accion" value="actualizar">
                <input type="hidden" name="id" value="<?= (int) $odontologo['id'] ?>">

                <div class="mb-3">
                    <label for="nombre" class="form-label fw-semibold small text-dark">Nombre Completo</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-user-nurse text-muted"></i></span>
                        <input
                            type="text"
                            class="form-control border-start-0"
                            id="nombre"
                            name="nombre"
                            maxlength="100"
                            value="<?= htmlspecialchars($odontologo['nombre'], ENT_QUOTES, 'UTF-8') ?>"
                            required
                            placeholder="Ej. Dra. Ana Torres"
                        >
                    </div>
                </div>

                <div class="mb-3">
                    <label for="especialidad" class="form-label fw-semibold small text-dark">Especialidad Clínica</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-stethoscope text-muted"></i></span>
                        <input
                            type="text"
                            class="form-control border-start-0"
                            id="especialidad"
                            name="especialidad"
                            maxlength="60"
                            value="<?= htmlspecialchars($odontologo['especialidad'], ENT_QUOTES, 'UTF-8') ?>"
                            required
                            placeholder="Ej. Ortodoncia, Endodoncia, Odontopediatría"
                        >
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label for="consultorio" class="form-label fw-semibold small text-dark">Consultorio / Espacio</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-door-closed text-muted"></i></span>
                            <input
                                type="text"
                                class="form-control border-start-0"
                                id="consultorio"
                                name="consultorio"
                                maxlength="30"
                                value="<?= htmlspecialchars($odontologo['consultorio'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                placeholder="Ej. Consultorio 1"
                            >
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label for="activo" class="form-label fw-semibold small text-dark">Estado de Cuenta</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-toggle-on text-muted"></i></span>
                            <select class="form-select border-start-0" id="activo" name="activo">
                                <option value="1" <?= !empty($odontologo['activo']) ? 'selected' : '' ?>>Activo (Habilitado)</option>
                                <option value="0" <?= empty($odontologo['activo']) ? 'selected' : '' ?>>Inactivo (Deshabilitado)</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label fw-semibold small text-dark">Correo Electrónico (Acceso)</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-envelope text-muted"></i></span>
                        <input
                            type="email"
                            class="form-control border-start-0"
                            id="email"
                            name="email"
                            maxlength="100"
                            value="<?= htmlspecialchars($odontologo['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                            required
                            placeholder="especialista@clinica.com"
                        >
                    </div>
                </div>

                <div class="p-3 bg-light rounded-3 border mb-4">
                    <div class="d-flex align-items-center text-muted small mb-1">
                        <i class="fa-solid fa-link me-2 text-primary"></i>
                        <span class="fw-semibold text-dark">Enlace público (Slug):</span>
                        <span class="badge bg-white text-dark border ms-2 font-monospace"><?= htmlspecialchars($odontologo['slug_publico'], ENT_QUOTES, 'UTF-8') ?></span>
                    </div>
                    <small class="text-muted d-block" style="font-size: 0.8rem;">
                        <i class="fa-solid fa-circle-info me-1"></i> El enlace se actualiza de forma automática si modificas el nombre del especialista.
                    </small>
                </div>

                <div class="d-grid gap-2 pt-1">
                    <button type="submit" class="btn btn--primary py-2 fw-bold rounded-3 shadow-sm">
                        <i class="fa-solid fa-floppy-disk me-2"></i> Guardar cambios
                    </button>
                    <a href="lista_odontologos.php" class="btn btn-light py-2 text-muted fw-semibold rounded-3 text-center">
                        Cancelar y volver al listado
                    </a>
                </div>
            </form>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../assests/js/dentix-i18n.js?v=20261110-cur"></script>
</body>
</html>
