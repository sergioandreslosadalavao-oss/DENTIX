<?php
// view/cita/lista_citas.php - Módulo de Citas con la estructura exacta y nativa de Servicios
require_once __DIR__ . '/../../app/recepcionista_auth.php';
requerir_recepcionista('../recepcionista/lista_recepcionista.php');
require_once "../../config/database.php";

$mensaje = "";
$error = "";

// Asignar un odontólogo confirma la solicitud de cita.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['actualizar_cita'])) {
    $citaId = filter_var($_POST['cita_id'] ?? null, FILTER_VALIDATE_INT);
    $odontologoId = filter_var($_POST['odontologo_id'] ?? null, FILTER_VALIDATE_INT);

    if (!validar_csrf_recepcionista()) {
        $error = "La sesión del formulario venció. Recarga la página e inténtalo de nuevo.";
    } elseif ($citaId === false || $citaId <= 0 || $odontologoId === false || $odontologoId <= 0) {
        $error = "Selecciona un odontólogo válido para confirmar la cita.";
    } else {
        try {
            $stmt = $conexion->prepare("SELECT id FROM odontologo WHERE id = ? AND activo IS TRUE");
            $stmt->execute([$odontologoId]);

            if (!$stmt->fetchColumn()) {
                $error = "El odontólogo seleccionado no está disponible.";
            } else {
                // Verificar la fecha de la cita para validar que el odontólogo no tenga ya otra cita ese mismo día
                $stmtFecha = $conexion->prepare("SELECT fecha FROM cita WHERE id = ?");
                $stmtFecha->execute([$citaId]);
                $fechaCita = $stmtFecha->fetchColumn();

                $stmtConflicto = $conexion->prepare(
                    "SELECT id FROM cita 
                     WHERE odontologo_id = ? 
                       AND fecha = ? 
                       AND id <> ? 
                       AND estado IN ('confirmada', 'completada')
                     LIMIT 1"
                );
                $stmtConflicto->execute([$odontologoId, $fechaCita, $citaId]);

                if ($stmtConflicto->fetchColumn()) {
                    $error = "El odontólogo seleccionado ya tiene una cita asignada para el día {$fechaCita}. Cada odontólogo solo puede atender máximo 1 cita por día.";
                } else {
                    $stmt = $conexion->prepare(
                        "UPDATE cita
                         SET odontologo_id = ?, estado = 'confirmada', recepcionista_id = ?
                         WHERE id = ? AND estado = 'pendiente'
                         RETURNING id"
                    );
                    $stmt->execute([$odontologoId, $_SESSION['recepcionista_id'], $citaId]);

                    if ($stmt->fetchColumn()) {
                        $mensaje = "La cita fue confirmada y el odontólogo quedó asignado.";
                    } else {
                        $error = "La cita ya no está pendiente. Actualiza la página para ver su estado.";
                    }
                }
            }
        } catch (PDOException $e) {
            error_log("Error al confirmar y asignar una cita: " . $e->getMessage());
            $error = "No se pudo confirmar la cita. Verifica que el odontólogo no tenga otra cita en ese horario.";
        }
    }
}

// Solicitudes que todavía requieren confirmación y asignación.
try {
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
} catch (PDOException $e) {
    $citasPendientes = [];
    $error = "Error al cargar las solicitudes pendientes.";
    error_log("Error al cargar citas pendientes: " . $e->getMessage());
}

// Citas confirmadas por recepción, mostradas debajo de las solicitudes pendientes.
try {
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
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Citas - SCO</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@500;600;700;800&family=Sora:wght@700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="../../assests/css/admin.css?v=20261103-2">
    <link rel="stylesheet" href="../../assests/css/index.css">
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
            </nav>
            <?php require __DIR__ . '/../recepcionista/boton_cerrar_sesion.php'; ?>
        </aside>

        <!-- CONTENIDO PRINCIPAL CON EL SHELL DE ADMIN -->
        <main class="app-main">
            <header class="topbar">
                <button class="menu-btn" id="menu-toggle"><span></span><span></span><span></span></button>
                <div class="topbar__crumbs"><a href="../../index.php">Inicio</a><span>/</span><strong>Citas</strong></div>
                <?php require __DIR__ . '/../recepcionista/perfil_topbar.php'; ?>
            </header>

            <div class="page">
                
                <?php if (!empty($mensaje)): ?>
                    <div class="alert alert-success py-2 small mb-3 border-0 shadow-sm">
                        <i class="fa-solid fa-circle-check me-2"></i> <?php echo $mensaje; ?>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger py-2 small mb-3 border-0 shadow-sm">
                        <i class="fa-solid fa-triangle-exclamation me-2"></i> <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <div class="panel">
                    <div class="panel__head">
                        <div>
                            <h3 class="panel__title"><i class="fa-solid fa-calendar-check"></i> Catálogo de Citas</h3>
                            <p class="panel__subtitle">Solicitudes pendientes de confirmación y asignación de odontólogo.</p>
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
                                                <div class="text-muted small"><i class="fa-solid fa-id-card me-1"></i> Cédula: <?php echo htmlspecialchars($cita['cedula'], ENT_QUOTES, 'UTF-8'); ?></div>
                                                <div class="text-muted small"><i class="fa-solid fa-tooth me-1"></i> <?php echo htmlspecialchars($cita['servicio_nombre'], ENT_QUOTES, 'UTF-8'); ?></div>
                                            </td>
                                            <td data-label="Fecha y Hora">
                                                <div><i class="fa-solid fa-calendar-days text-teal me-1"></i> <strong><?php echo htmlspecialchars($cita['fecha'], ENT_QUOTES, 'UTF-8'); ?></strong></div>
                                                <div class="text-muted small"><i class="fa-solid fa-clock me-1"></i> <?php echo htmlspecialchars(substr((string) $cita['hora'], 0, 5), ENT_QUOTES, 'UTF-8'); ?></div>
                                            </td>
                                            <td data-label="Odontólogo">
                                                <form action="lista_citas.php" method="POST" class="d-flex gap-2 align-items-center" id="form-cita-<?php echo (int) $cita['id']; ?>">
                                                    <input type="hidden" name="actualizar_cita" value="1">
                                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
                                                    <input type="hidden" name="cita_id" value="<?php echo (int) $cita['id']; ?>">
                                                    
                                                    <select name="odontologo_id" class="form-select form-select-sm" required style="border-radius: 8px;">
                                                        <option value="">-- Seleccionar --</option>
                                                        <?php foreach ($odontologosActivos as $odonto): ?>
                                                            <option value="<?php echo (int) $odonto['id']; ?>" <?php echo ((int) $cita['odontologo_id'] === (int) $odonto['id']) ? 'selected' : ''; ?>>
                                                                <?php echo htmlspecialchars($odonto['nombre'] . ' (' . $odonto['especialidad'] . ')', ENT_QUOTES, 'UTF-8'); ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                            </td>
                                            <td data-label="Acciones" class="text-center">
                                                    <button type="submit" class="btn btn--primary btn--sm" style="display: inline-flex; align-items: center; gap: 6px;">
                                                        <i class="fa-solid fa-check"></i> Confirmar
                                                    </button>
                                                </form>
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
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($citasConfirmadas !== []): ?>
                                    <?php foreach ($citasConfirmadas as $index => $cita): ?>
                                        <tr>
                                            <td data-label="#"><span class="row-id"><?php echo $index + 1; ?></span></td>
                                            <td data-label="Paciente">
                                                <strong><?php echo htmlspecialchars($cita['paciente_nombre'], ENT_QUOTES, 'UTF-8'); ?></strong>
                                                <div class="text-muted small"><i class="fa-solid fa-id-card me-1"></i> Cédula: <?php echo htmlspecialchars($cita['cedula'], ENT_QUOTES, 'UTF-8'); ?></div>
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
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">Todavía no hay citas confirmadas.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
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
    <script src="../../assests/js/admin-animations.js"></script>
</body>
</html>