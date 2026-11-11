<?php
require_once __DIR__ . '/../../app/recepcionista_auth.php';
requerir_recepcionista('../recepcionista/lista_recepcionista.php');

// Si se accede directamente con ?id=... (sin pasar por app/logica_paciente.php), cargamos el paciente aquí.
if (!isset($paciente) || !is_array($paciente)) {
    require_once __DIR__ . '/../../config/database.php';

    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    if ($id === false || $id === null || $id <= 0) {
        $_SESSION['mensaje'] = 'Error: el ID del paciente no es válido.';
        $_SESSION['tipo_mensaje'] = 'error';
        header('Location: lista_pacientes.php');
        exit;
    }

    try {
        $stmt = $conexion->prepare(
            'SELECT id, nombre, cedula, telefono, email, fecha_nac, alergias
             FROM paciente
             WHERE id = :id'
        );
        $stmt->execute([':id' => $id]);
        $paciente = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log('Error al consultar paciente en editar_paciente.php: ' . $e->getMessage());
        $paciente = false;
    }

    if (!$paciente) {
        $_SESSION['mensaje'] = 'No se encontró el paciente solicitado.';
        $_SESSION['tipo_mensaje'] = 'error';
        header('Location: lista_pacientes.php');
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
    <title>Editar Paciente - DENTIX</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@500;600;700;800&family=Sora:wght@700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="../../assests/css/admin.css">
    <link rel="stylesheet" href="../../assests/css/index.css">
</head>
<body class="admin-body" style="display: grid; place-items: center; min-height: 100vh; padding: 30px 15px;">

    <div class="container" style="max-width: 600px;">
        <div class="form-card shadow-lg p-4 p-md-5 border-0 rounded-4 bg-white">
            
            <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
                <div>
                    <span class="eyebrow mb-1 text-primary fw-bold" style="font-size: 0.75rem; letter-spacing: 0.05em; text-transform: uppercase;">Expediente de Pacientes</span>
                    <h2 class="fw-bold text-dark mb-0 fs-3" style="font-family: 'Sora', sans-serif;">
                        <i class="fa-solid fa-hospital-user text-primary me-2"></i> Editar Paciente
                    </h2>
                </div>
                <a href="lista_pacientes.php" class="btn btn-outline-secondary btn-sm rounded-pill px-3 fw-semibold">
                    <i class="fa-solid fa-arrow-left me-1"></i> Volver
                </a>
            </div>

            <?php if ($mensaje !== null): ?>
                <div class="alert <?= $tipo_mensaje === 'error' ? 'alert-danger' : 'alert-success' ?> py-2 mb-4 small d-flex align-items-center rounded-3" role="alert">
                    <i class="fa-solid <?= $tipo_mensaje === 'error' ? 'fa-triangle-exclamation' : 'fa-circle-check' ?> me-2 fs-5"></i>
                    <div><?= htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8') ?></div>
                </div>
            <?php endif; ?>

            <form action="../../app/logica_paciente.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="accion" value="actualizar">
                <input type="hidden" name="id" value="<?= (int) $paciente['id'] ?>">

                <div class="mb-3">
                    <label for="nombre" class="form-label fw-semibold small text-dark">Nombre Completo</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-user text-muted"></i></span>
                        <input
                            type="text"
                            class="form-control border-start-0"
                            id="nombre"
                            name="nombre"
                            maxlength="100"
                            value="<?= htmlspecialchars($paciente['nombre'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                            required
                        >
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label for="cedula" class="form-label fw-semibold small text-dark">Cédula / Documento</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-id-card text-muted"></i></span>
                            <input
                                type="text"
                                class="form-control border-start-0"
                                id="cedula"
                                name="cedula"
                                maxlength="20"
                                value="<?= htmlspecialchars($paciente['cedula'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                required
                            >
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label for="telefono" class="form-label fw-semibold small text-dark">Teléfono / Celular</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-phone text-muted"></i></span>
                            <input
                                type="text"
                                class="form-control border-start-0"
                                id="telefono"
                                name="telefono"
                                maxlength="20"
                                value="<?= htmlspecialchars($paciente['telefono'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                required
                            >
                        </div>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-7">
                        <label for="email" class="form-label fw-semibold small text-dark">Correo Electrónico</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-envelope text-muted"></i></span>
                            <input
                                type="email"
                                class="form-control border-start-0"
                                id="email"
                                name="email"
                                maxlength="100"
                                value="<?= htmlspecialchars($paciente['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                placeholder="correo@ejemplo.com"
                            >
                        </div>
                    </div>

                    <div class="col-md-5">
                        <label for="fecha_nac" class="form-label fw-semibold small text-dark">Fecha de Nacimiento</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-cake-candles text-muted"></i></span>
                            <input
                                type="date"
                                class="form-control border-start-0"
                                id="fecha_nac"
                                name="fecha_nac"
                                max="<?= date('Y-m-d') ?>"
                                value="<?= htmlspecialchars($paciente['fecha_nac'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                            >
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <label for="alergias" class="form-label fw-semibold small text-dark">Alergias / Antecedentes</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 align-items-start pt-2"><i class="fa-solid fa-notes-medical text-muted"></i></span>
                        <textarea
                            class="form-control border-start-0"
                            id="alergias"
                            name="alergias"
                            rows="3"
                            placeholder="Ej. Penicilina, látex, o dejar vacío si ninguna..."
                        ><?= htmlspecialchars($paciente['alergias'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                    </div>
                </div>

                <div class="d-grid gap-2 pt-1">
                    <button type="submit" class="btn btn--primary py-2 fw-bold rounded-3 shadow-sm">
                        <i class="fa-solid fa-floppy-disk me-2"></i> Guardar cambios
                    </button>
                    <a href="lista_pacientes.php" class="btn btn-light py-2 text-muted fw-semibold rounded-3 text-center">
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
