<?php
// view/cita/lista_citas.php - Módulo de Citas con la estructura exacta y nativa de Servicios
require_once __DIR__ . '/../../app/recepcionista_auth.php';
requerir_recepcionista('../recepcionista/lista_recepcionista.php');
require_once "../../config/database.php";
require_once __DIR__ . '/../../app/odontologo_catalogos.php';

$mensaje = "";
$error = "";

if (isset($_SESSION['mensaje'])) {
    if (($_SESSION['tipo_mensaje'] ?? 'exito') === 'error') {
        $error = (string) $_SESSION['mensaje'];
    } else {
        $mensaje = (string) $_SESSION['mensaje'];
    }
    unset($_SESSION['mensaje'], $_SESSION['tipo_mensaje']);
}

// Cualquier petición POST se delega al controlador de lógica de negocio (separación estricta de capas)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require __DIR__ . '/../../app/logica_cita.php';
    exit();
}

// Solicitudes que todavía requieren confirmación y asignación (consultadas desde la vista optimizada v_agenda_dia).
try {
    try {
        $stmt = $conexion->query(
            "SELECT cita_id AS id, fecha, hora, paciente AS paciente_nombre, cedula,
                    servicio AS servicio_nombre, odontologo_id
             FROM v_agenda_dia
             WHERE estado = 'pendiente'
             ORDER BY fecha, hora, cita_id"
        );
        $citasPendientes = $stmt->fetchAll();
    } catch (PDOException $eVistaPend) {
        $stmt = $conexion->query(
            "SELECT c.id, c.fecha, c.hora, p.nombre AS paciente_nombre, p.cedula,
                    s.nombre AS servicio_nombre, o.id AS odontologo_id
             FROM cita c
             JOIN paciente p ON p.id = c.paciente_id
             JOIN servicio s ON s.id = c.servicio_id
             LEFT JOIN odontologo o ON o.id = c.odontologo_id
             WHERE c.estado = 'pendiente'
             ORDER BY c.fecha, c.hora, c.id"
        );
        $citasPendientes = $stmt->fetchAll();
    }
} catch (PDOException $e) {
    $citasPendientes = [];
    $error = "Error al cargar las solicitudes pendientes.";
    error_log("Error al cargar citas pendientes: " . $e->getMessage());
}

// Citas confirmadas por recepción, consultadas desde la vista v_agenda_dia.
try {
    try {
        $stmt = $conexion->query(
            "SELECT cita_id AS id, fecha, hora, paciente AS paciente_nombre, cedula,
                    servicio AS servicio_nombre, odontologo AS odontologo_nombre,
                    odontologo_especialidad
             FROM v_agenda_dia
             WHERE estado = 'confirmada' AND odontologo_id IS NOT NULL
             ORDER BY fecha DESC, hora DESC, cita_id DESC"
        );
        $citasConfirmadas = $stmt->fetchAll();
    } catch (PDOException $eVistaConf) {
        $stmt = $conexion->query(
            "SELECT c.id, c.fecha, c.hora, p.nombre AS paciente_nombre, p.cedula,
                    s.nombre AS servicio_nombre, o.nombre AS odontologo_nombre,
                    o.especialidad AS odontologo_especialidad
             FROM cita c
             JOIN paciente p ON p.id = c.paciente_id
             JOIN servicio s ON s.id = c.servicio_id
             JOIN odontologo o ON o.id = c.odontologo_id
             WHERE c.estado = 'confirmada' AND c.odontologo_id IS NOT NULL
             ORDER BY c.fecha DESC, c.hora DESC, c.id DESC"
        );
        $citasConfirmadas = $stmt->fetchAll();
    }
} catch (PDOException $e) {
    $citasConfirmadas = [];
    $error = "Error al cargar las citas confirmadas.";
    error_log("Error al cargar citas confirmadas: " . $e->getMessage());
}

try {
    $stmt = $conexion->query(
        "SELECT id, nombre, especialidad
         FROM odontologo
         WHERE activo IS TRUE
         ORDER BY nombre"
    );
    $odontologosActivos = $stmt->fetchAll();
} catch (PDOException $e) {
    $odontologosActivos = [];
    $error = "Error al cargar odontólogos disponibles.";
    error_log("Error al cargar odontólogos activos: " . $e->getMessage());
}
$csrfToken = token_csrf_recepcionista();
?>
<!DOCTYPE html>
<html lang="es" translate="no" class="notranslate">
<head>
    <meta charset="UTF-8">
    <meta name="google" content="notranslate">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Citas - DENTIX</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@500;600;700;800&family=Sora:wght@700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/admin.css?v=20261103-2">
    <link rel="stylesheet" href="../../assets/css/index.css">
</head>
<body class="admin-body" data-accent="citas">
    <div class="page-progress"><span class="page-progress__bar"></span></div>
    <div class="app-shell">
        
        <!-- BARRA LATERAL IDÉNTICA A SERVICIOS -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar__top">
                <a href="../../index.php" class="brand">
                    <div class="brand__mark"><i class="fa-solid fa-tooth"></i></div>
                    <div class="brand__text">DENTIX<small>Panel Admin</small></div>
                </a>
                <button class="sidebar__close" id="sidebar-close"><svg viewBox="0 0 24 24"><path d="M18 6L6 18M6 6l12 12"/></svg></button>
            </div>
            <nav class="sidebar__nav">
                <span class="sidebar__label">Navegación</span>
                <a href="../../index.php" class="side-link"><span class="side-link__icon"><i class="fa-solid fa-house"></i></span>Inicio</a>
                <a href="../paciente/lista_pacientes.php" class="side-link"><span class="side-link__icon"><i class="fa-solid fa-hospital-user"></i></span>Pacientes</a>
                <a href="../odontologo/lista_odontologos.php" class="side-link"><span class="side-link__icon"><i class="fa-solid fa-user-doctor"></i></span>Odontólogos</a>
                <a href="../servicio/lista_servicios.php" class="side-link"><span class="side-link__icon"><i class="fa-solid fa-notes-medical"></i></span>Servicios</a>
                <a href="lista_citas.php" class="side-link is-active"><span class="side-link__icon"><i class="fa-solid fa-calendar-days"></i></span>Citas</a>
                <a href="../recepcionista/reportes.php" class="side-link"><span class="side-link__icon"><i class="fa-solid fa-chart-column"></i></span>Reportes</a>
            </nav>
            <?php require __DIR__ . '/../recepcionista/boton_cerrar_sesion.php'; ?>
        </aside>

        <!-- CONTENIDO PRINCIPAL CON EL SHELL DE ADMIN -->
        <main class="app-main">
            <header class="topbar">
                <button class="menu-btn" id="menu-toggle"><span></span><span></span><span></span></button>
                <div class="topbar__crumbs"><a href="../../index.php">Inicio</a><span>/</span><strong>Citas</strong></div>
            </header>

            <div class="page">
                
                <?php if (!empty($mensaje)): ?>
                    <div class="alert alert-success py-2 small mb-3 border-0 shadow-sm">
                        <i class="fa-solid fa-circle-check me-2"></i> <?php echo htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger py-2 small mb-3 border-0 shadow-sm">
                        <i class="fa-solid fa-triangle-exclamation me-2"></i> <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                <?php endif; ?>

                <div class="panel">
                    <div class="panel__head">
                        <div>
                            <h3 class="panel__title"><i class="fa-solid fa-calendar-check"></i> Catálogo de Citas</h3>
                            <p class="panel__subtitle">Direccionamiento de solicitudes pendientes y asignación de odontólogo disponible.</p>
                        </div>
                        <div class="panel__tools">
                            <span class="badge bg-teal bg-opacity-10 text-teal px-3 py-2 rounded-pill fw-semibold" style="color: #0a7a70; background: #e6f9f3; font-size: 0.75rem;">
                                <i class="fa-solid fa-user-check me-1"></i> Agendamiento exclusivo por el paciente
                            </span>
                        </div>
                    </div>

                    <div class="table-wrap">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Paciente / Cédula</th>
                                    <th>Fecha y Hora</th>
                                    <th>Odontólogo Asignado</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($citasPendientes !== []): ?>
                                    <?php foreach ($citasPendientes as $index => $cita): ?>
                                        <tr>
                                            <td data-label="#"><span class="row-id"><?php echo $index + 1; ?></span></td>
                                            <td data-label="Paciente">
                                                <strong><?php echo htmlspecialchars($cita['paciente_nombre'], ENT_QUOTES, 'UTF-8'); ?></strong>
                                                <div class="text-muted small"><i class="fa-solid fa-id-card me-1"></i> Cédula: <?php echo htmlspecialchars($cita['cedula'] ?? 'Sin registro', ENT_QUOTES, 'UTF-8'); ?></div>
                                                <div class="text-muted small"><i class="fa-solid fa-tooth me-1"></i> <?php echo htmlspecialchars($cita['servicio_nombre'], ENT_QUOTES, 'UTF-8'); ?></div>
                                            </td>
                                            <td data-label="Fecha y Hora">
                                                <div><i class="fa-solid fa-calendar-days text-teal me-1"></i> <strong><?php echo htmlspecialchars($cita['fecha'], ENT_QUOTES, 'UTF-8'); ?></strong></div>
                                                <div class="text-muted small"><i class="fa-solid fa-clock me-1"></i> <?php echo htmlspecialchars(substr((string) $cita['hora'], 0, 5), ENT_QUOTES, 'UTF-8'); ?></div>
                                            </td>
                                            <td data-label="Odontólogo">
                                                <?php
                                                $servicioSolicitadoCita = trim((string) ($cita['servicio_nombre'] ?? ''));
                                                $odontologosCompatibles = array_values(array_filter(
                                                    $odontologosActivos,
                                                    static fn(array $odonto): bool => coincideEspecialidadConServicio(
                                                        (string) ($odonto['especialidad'] ?? ''),
                                                        $servicioSolicitadoCita
                                                    )
                                                ));
                                                $sinEspecialistaServicio = ($odontologosCompatibles === []);
                                                ?>
                                                <form action="../../app/logica_cita.php" method="POST" id="form-cita-<?php echo (int) $cita['id']; ?>" style="margin: 0;">
                                                    <input type="hidden" name="accion" value="confirmar">
                                                    <input type="hidden" name="actualizar_cita" value="1">
                                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
                                                    <input type="hidden" name="cita_id" value="<?php echo (int) $cita['id']; ?>">
                                                    <?php if (!$sinEspecialistaServicio): ?>
                                                        <select name="odontologo_id" class="form-select form-select-sm" required style="border-radius: 8px;">
                                                            <option value="">-- Especialista en <?php echo htmlspecialchars($servicioSolicitadoCita, ENT_QUOTES, 'UTF-8'); ?> --</option>
                                                            <?php foreach ($odontologosCompatibles as $odonto): ?>
                                                                <option value="<?php echo (int) $odonto['id']; ?>" <?php echo ((int) $cita['odontologo_id'] === (int) $odonto['id']) ? 'selected' : ''; ?>>
                                                                    <?php echo htmlspecialchars($odonto['nombre'] . ' (' . $odonto['especialidad'] . ')', ENT_QUOTES, 'UTF-8'); ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    <?php else: ?>
                                                        <select name="odontologo_id" class="form-select form-select-sm" disabled style="border-radius: 8px;">
                                                            <option value="">Sin odontólogo activo en <?php echo htmlspecialchars($servicioSolicitadoCita, ENT_QUOTES, 'UTF-8'); ?></option>
                                                        </select>
                                                        <div class="small mt-1" style="font-size: 0.74rem; color: #b45309;">
                                                            <i class="fa-solid fa-circle-info me-1"></i>Asigna esta especialidad a un odontólogo en <a href="../odontologo/lista_odontologos.php" class="fw-bold text-decoration-underline" style="color: #0f766e;">Odontólogos</a>.
                                                        </div>
                                                    <?php endif; ?>
                                                </form>
                                            </td>
                                            <td data-label="Acciones" class="text-center">
                                                <div class="d-inline-flex gap-1 align-items-center justify-content-center flex-wrap">
                                                    <button type="submit" form="form-cita-<?php echo (int) $cita['id']; ?>" class="btn btn--primary btn--sm" style="display: inline-flex; align-items: center; gap: 5px;" <?php echo $sinEspecialistaServicio ? 'disabled title="No hay odontólogos activos con la especialidad solicitada"' : ''; ?>>
                                                        <i class="fa-solid fa-check"></i> Confirmar
                                                    </button>
                                                    <button type="button" class="action action--delete border-0" data-bs-toggle="modal" data-bs-target="#modalCancelarCita" data-id="<?php echo (int) $cita['id']; ?>" data-paciente="<?php echo htmlspecialchars($cita['paciente_nombre'], ENT_QUOTES, 'UTF-8'); ?>" data-fecha="<?php echo htmlspecialchars($cita['fecha'] . ' ' . substr((string) $cita['hora'], 0, 5), ENT_QUOTES, 'UTF-8'); ?>">
                                                        <i class="fa-solid fa-ban"></i> Cancelar
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">No hay solicitudes pendientes de confirmar.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="panel mt-4">
                    <div class="panel__head">
                        <div>
                            <h3 class="panel__title"><i class="fa-solid fa-circle-check"></i> Citas confirmadas</h3>
                            <p class="panel__subtitle">Citas confirmadas por recepción con su odontólogo asignado.</p>
                        </div>
                        <span class="row-id"><?php echo count($citasConfirmadas); ?></span>
                    </div>

                    <div class="table-wrap">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Paciente / Cédula</th>
                                    <th>Servicio</th>
                                    <th>Fecha y Hora</th>
                                    <th>Odontólogo asignado</th>
                                    <th>Estado</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($citasConfirmadas !== []): ?>
                                    <?php foreach ($citasConfirmadas as $index => $cita): ?>
                                        <tr>
                                            <td data-label="#"><span class="row-id"><?php echo $index + 1; ?></span></td>
                                            <td data-label="Paciente">
                                                <strong><?php echo htmlspecialchars($cita['paciente_nombre'], ENT_QUOTES, 'UTF-8'); ?></strong>
                                                <div class="text-muted small"><i class="fa-solid fa-id-card me-1"></i> Cédula: <?php echo htmlspecialchars($cita['cedula'] ?? 'Sin registro', ENT_QUOTES, 'UTF-8'); ?></div>
                                            </td>
                                            <td data-label="Servicio"><?php echo htmlspecialchars($cita['servicio_nombre'], ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td data-label="Fecha y Hora">
                                                <div><i class="fa-solid fa-calendar-days text-teal me-1"></i> <strong><?php echo htmlspecialchars($cita['fecha'], ENT_QUOTES, 'UTF-8'); ?></strong></div>
                                                <div class="text-muted small"><i class="fa-solid fa-clock me-1"></i> <?php echo htmlspecialchars(substr((string) $cita['hora'], 0, 5), ENT_QUOTES, 'UTF-8'); ?></div>
                                            </td>
                                            <td data-label="Odontólogo asignado">
                                                <strong><?php echo htmlspecialchars($cita['odontologo_nombre'], ENT_QUOTES, 'UTF-8'); ?></strong>
                                                <div class="text-muted small"><?php echo htmlspecialchars($cita['odontologo_especialidad'], ENT_QUOTES, 'UTF-8'); ?></div>
                                            </td>
                                            <td data-label="Estado"><span class="badge bg-success-subtle text-success">Confirmada</span></td>
                                            <td data-label="Acciones" class="text-center">
                                                <div class="d-inline-flex gap-1 align-items-center justify-content-center flex-wrap">
                                                    <form action="../../app/logica_cita.php" method="POST" style="margin: 0;" onsubmit="return confirm('¿Confirmas marcar esta cita como inasistencia (No asistió)?');">
                                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
                                                        <input type="hidden" name="accion" value="marcar_no_show">
                                                        <input type="hidden" name="id" value="<?php echo (int) $cita['id']; ?>">
                                                        <button type="submit" class="action border-0" style="background: #fef3c7; color: #92400e;">
                                                            <i class="fa-solid fa-user-xmark"></i> No asistió
                                                        </button>
                                                    </form>
                                                    <button type="button" class="action action--delete border-0" data-bs-toggle="modal" data-bs-target="#modalCancelarCita" data-id="<?php echo (int) $cita['id']; ?>" data-paciente="<?php echo htmlspecialchars($cita['paciente_nombre'], ENT_QUOTES, 'UTF-8'); ?>" data-fecha="<?php echo htmlspecialchars($cita['fecha'] . ' ' . substr((string) $cita['hora'], 0, 5), ENT_QUOTES, 'UTF-8'); ?>">
                                                        <i class="fa-solid fa-ban"></i> Cancelar
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-4">Todavía no hay citas confirmadas.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Modal para cancelar cita exigiendo motivo (RN-06) -->
            <div class="modal fade" id="modalCancelarCita" tabindex="-1" aria-labelledby="modalCancelarCitaLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border-0 shadow-lg rounded-4">
                        <div class="modal-header border-0 pb-0">
                            <h5 class="modal-title fw-bold text-danger" id="modalCancelarCitaLabel">
                                <i class="fa-solid fa-ban me-2"></i>Cancelar Cita
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                        </div>
                        <form action="../../app/logica_cita.php" method="POST">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
                            <input type="hidden" name="accion" value="cancelar">
                            <input type="hidden" name="id" id="cancel_cita_id" value="">
                            <div class="modal-body">
                                <p class="small text-muted mb-3">
                                    Paciente: <strong id="cancel_paciente" class="text-dark"></strong><br>
                                    Fecha y hora: <strong id="cancel_fecha" class="text-dark"></strong>
                                </p>
                                <div class="mb-2">
                                    <label for="motivo_cancelacion" class="form-label fw-semibold small text-dark">Motivo de cancelación (obligatorio)</label>
                                    <textarea class="form-control" id="motivo_cancelacion" name="motivo_cancelacion" rows="3" maxlength="200" required placeholder="Ej. El paciente solicitó reprogramar por motivos personales."></textarea>
                                </div>
                            </div>
                            <div class="modal-footer border-0 pt-0">
                                <button type="button" class="btn btn-light rounded-3" data-bs-dismiss="modal">Cerrar</button>
                                <button type="submit" class="btn btn-danger rounded-3 fw-semibold">Confirmar cancelación</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <footer class="app-footer">
                <div>&copy; 2026 DENTIX</div>
                <div>ADSO - SENA</div>
            </footer>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
    <script src="../../assets/js/admin-animations.js"></script>
    <script>
        const modalCancelar = document.getElementById('modalCancelarCita');
        if (modalCancelar) {
            modalCancelar.addEventListener('show.bs.modal', function (event) {
                const btn = event.relatedTarget;
                document.getElementById('cancel_cita_id').value = btn.getAttribute('data-id') || '';
                document.getElementById('cancel_paciente').textContent = btn.getAttribute('data-paciente') || '';
                document.getElementById('cancel_fecha').textContent = btn.getAttribute('data-fecha') || '';
                document.getElementById('motivo_cancelacion').value = '';
            });
        }
    </script>
    <script src="../../assets/js/dentix-i18n.js?v=20261120-i18nfix"></script>
</body>
</html>