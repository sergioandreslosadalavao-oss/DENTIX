<?php
require_once __DIR__ . '/../../app/recepcionista_auth.php';
requerir_recepcionista('../recepcionista/lista_recepcionista.php');
require_once __DIR__ . '/../../config/database.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if ($id === false || $id === null || $id <= 0) {
    $_SESSION['mensaje'] = 'Error: el ID de la cita no es válido.';
    $_SESSION['tipo_mensaje'] = 'error';
    header('Location: lista_citas.php');
    exit();
}

try {
    $stmt = $conexion->prepare('SELECT * FROM cita WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $cita = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$cita) {
        $_SESSION['mensaje'] = 'No se encontró la cita solicitada.';
        $_SESSION['tipo_mensaje'] = 'error';
        header('Location: lista_citas.php');
        exit();
    }

    $pacientes = $conexion->query('SELECT id, nombre, cedula FROM paciente ORDER BY nombre ASC')->fetchAll();
    $odontologos = $conexion->query('SELECT id, nombre, especialidad FROM odontologo WHERE activo IS TRUE ORDER BY nombre ASC')->fetchAll();
    $servicios = $conexion->query('SELECT id, nombre FROM servicio ORDER BY nombre ASC')->fetchAll();
} catch (PDOException $e) {
    error_log('Error al consultar cita en editar_cita.php: ' . $e->getMessage());
    $error = 'No se pudieron cargar los datos de la cita.';
    $pacientes = $odontologos = $servicios = [];
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
    <title>Editar Cita - DENTIX</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@500;600;700;800&family=Sora:wght@700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="../../assests/css/admin.css">
    <link rel="stylesheet" href="../../assests/css/index.css">
</head>
<body class="admin-body" style="display: grid; place-items: center; min-height: 100vh; padding: 30px 15px;">

    <div class="container" style="max-width: 640px;">
        <div class="form-card shadow-lg p-4 p-md-5 border-0 rounded-4 bg-white">
            <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
                <div>
                    <span class="eyebrow mb-1 text-primary fw-bold" style="font-size: 0.75rem; letter-spacing: 0.05em; text-transform: uppercase;">Agenda Clínica</span>
                    <h2 class="fw-bold text-dark mb-0 fs-3" style="font-family: 'Sora', sans-serif;">
                        <i class="fa-solid fa-calendar-pen text-primary me-2"></i> Editar Cita #<?php echo (int) $cita['id']; ?>
                    </h2>
                </div>
                <a href="lista_citas.php" class="btn btn-outline-secondary btn-sm rounded-pill px-3 fw-semibold">
                    <i class="fa-solid fa-arrow-left me-1"></i> Volver
                </a>
            </div>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger py-2 mb-4 small"><i class="fa-solid fa-triangle-exclamation me-2"></i><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
            <?php endif; ?>

            <?php if ($mensaje !== null): ?>
                <div class="alert <?php echo $tipo_mensaje === 'error' ? 'alert-danger' : 'alert-success'; ?> py-2 mb-4 small d-flex align-items-center rounded-3" role="alert">
                    <i class="fa-solid <?php echo $tipo_mensaje === 'error' ? 'fa-triangle-exclamation' : 'fa-circle-check'; ?> me-2 fs-5"></i>
                    <div><?php echo htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8'); ?></div>
                </div>
            <?php endif; ?>

            <form action="../../app/logica_cita.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
                <input type="hidden" name="accion" value="actualizar">
                <input type="hidden" name="id" value="<?php echo (int) $cita['id']; ?>">

                <div class="mb-3">
                    <label for="paciente_id" class="form-label fw-semibold small text-dark"><i class="fa-solid fa-user me-1 text-muted"></i> Paciente</label>
                    <select name="paciente_id" id="paciente_id" class="form-select" required>
                        <?php foreach ($pacientes as $p): ?>
                            <option value="<?php echo (int) $p['id']; ?>" <?php echo ((int) $p['id'] === (int) $cita['paciente_id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($p['nombre'] . (!empty($p['cedula']) ? ' — CC ' . $p['cedula'] : ''), ENT_QUOTES, 'UTF-8'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="odontologo_id" class="form-label fw-semibold small text-dark"><i class="fa-solid fa-user-doctor me-1 text-muted"></i> Odontólogo Asignado</label>
                    <select name="odontologo_id" id="odontologo_id" class="form-select">
                        <option value="">Por asignar (sin odontólogo)</option>
                        <?php foreach ($odontologos as $o): ?>
                            <option value="<?php echo (int) $o['id']; ?>" <?php echo ($cita['odontologo_id'] !== null && (int) $o['id'] === (int) $cita['odontologo_id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($o['nombre'] . ' (' . $o['especialidad'] . ')', ENT_QUOTES, 'UTF-8'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="servicio_id" class="form-label fw-semibold small text-dark"><i class="fa-solid fa-tooth me-1 text-muted"></i> Servicio Odontológico</label>
                    <select name="servicio_id" id="servicio_id" class="form-select" required>
                        <?php foreach ($servicios as $s): ?>
                            <option value="<?php echo (int) $s['id']; ?>" <?php echo ((int) $s['id'] === (int) $cita['servicio_id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($s['nombre'], ENT_QUOTES, 'UTF-8'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label for="fecha" class="form-label fw-semibold small text-dark"><i class="fa-solid fa-calendar me-1 text-muted"></i> Fecha</label>
                        <input type="date" name="fecha" id="fecha" class="form-control" value="<?php echo htmlspecialchars($cita['fecha'], ENT_QUOTES, 'UTF-8'); ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label for="hora" class="form-label fw-semibold small text-dark"><i class="fa-solid fa-clock me-1 text-muted"></i> Hora (08:00 – 19:30)</label>
                        <input type="time" name="hora" id="hora" class="form-control" min="08:00" max="19:30" step="1800" value="<?php echo htmlspecialchars(substr((string) $cita['hora'], 0, 5), ENT_QUOTES, 'UTF-8'); ?>" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="estado" class="form-label fw-semibold small text-dark"><i class="fa-solid fa-info-circle me-1 text-muted"></i> Estado de la Cita</label>
                    <select name="estado" id="estado" class="form-select" required>
                        <option value="pendiente" <?php echo ($cita['estado'] === 'pendiente') ? 'selected' : ''; ?>>Pendiente</option>
                        <option value="confirmada" <?php echo ($cita['estado'] === 'confirmada') ? 'selected' : ''; ?>>Confirmada</option>
                        <option value="completada" <?php echo ($cita['estado'] === 'completada') ? 'selected' : ''; ?>>Completada</option>
                        <option value="cancelada" <?php echo ($cita['estado'] === 'cancelada') ? 'selected' : ''; ?>>Cancelada</option>
                        <option value="no_show" <?php echo ($cita['estado'] === 'no_show') ? 'selected' : ''; ?>>No Asistió (No Show)</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="motivo" class="form-label fw-semibold small text-dark"><i class="fa-solid fa-comment-medical me-1 text-muted"></i> Motivo de Consulta</label>
                    <input type="text" name="motivo" id="motivo" maxlength="200" class="form-control" value="<?php echo htmlspecialchars($cita['motivo'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                </div>

                <div class="mb-4" id="grupo_motivo_cancelacion">
                    <label for="motivo_cancelacion" class="form-label fw-semibold small text-dark"><i class="fa-solid fa-ban me-1 text-danger"></i> Motivo de Cancelación (obligatorio si el estado es Cancelada)</label>
                    <input type="text" name="motivo_cancelacion" id="motivo_cancelacion" maxlength="200" class="form-control" value="<?php echo htmlspecialchars($cita['motivo_cancelacion'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="Ej. Paciente reprograma por viaje">
                </div>

                <div class="d-grid gap-2 mt-4">
                    <button type="submit" class="btn btn--primary py-2 fw-bold rounded-3 shadow-sm">
                        <i class="fa-solid fa-floppy-disk me-2"></i>Guardar Cambios
                    </button>
                    <a href="lista_citas.php" class="btn btn-light py-2 text-muted fw-semibold rounded-3 text-center">
                        Cancelar y volver
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../assests/js/dentix-i18n.js?v=20261110-cur"></script>
</body>
</html>
