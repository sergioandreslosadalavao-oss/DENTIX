<?php
// view/cita/lista_citas.php - Módulo de Citas con la estructura exacta y nativa de Servicios
require_once "../../config/database.php";
session_start();

$mensaje = "";
$error = "";

// 1. Asignar odontólogo o confirmar una cita pendiente.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion_cita'])) {
    $accion = $_POST['accion_cita'];
    $cita_id = $_POST['cita_id'] ?? '';
    $odontologo_id = $_POST['odontologo_id'] ?? '';

    if (
        in_array($accion, ['asignar', 'confirmar'], true)
        && ctype_digit((string) $cita_id)
        && ctype_digit((string) $odontologo_id)
    ) {
        try {
            $conexion->beginTransaction();

            $stmt_odonto = $conexion->prepare("SELECT id FROM odontologo WHERE id = ? AND activo = TRUE");
            $stmt_odonto->execute([$odontologo_id]);

            if (!$stmt_odonto->fetch()) {
                $conexion->rollBack();
                $error = "El odontólogo seleccionado no está activo. Actualiza la página e inténtalo de nuevo.";
            } else {
                $nuevo_estado = $accion === 'confirmar' ? 'confirmada' : 'pendiente';
                $stmt_update = $conexion->prepare(
                    "UPDATE cita
                     SET odontologo_id = ?, estado = ?
                     WHERE id = ? AND estado = 'pendiente'
                     RETURNING id"
                );
                $stmt_update->execute([$odontologo_id, $nuevo_estado, $cita_id]);
                $cita_actualizada = $stmt_update->fetchColumn();

                if ($cita_actualizada !== false) {
                    $conexion->commit();
                    $mensaje = $accion === 'confirmar'
                        ? "¡Cita confirmada! Ya aparece en Citas Confirmadas."
                        : "¡Odontólogo asignado a la cita!";
                } else {
                    $conexion->rollBack();
                    $stmt_cita = $conexion->prepare("SELECT estado FROM cita WHERE id = ?");
                    $stmt_cita->execute([$cita_id]);
                    $estado_actual = $stmt_cita->fetchColumn();
                    $error = $estado_actual === false
                        ? "No se encontró la cita que intentas actualizar."
                        : "Esta cita ya no está pendiente. Actualiza la página para ver su estado.";
                }
            }
        } catch (PDOException $e) {
            if ($conexion->inTransaction()) {
                $conexion->rollBack();
            }
            error_log("Error al asignar odontólogo a la cita: " . $e->getMessage());
            $error = "No se pudo guardar la cita. Verifica que el odontólogo no tenga otra cita en ese horario.";
        }
    } else {
        $error = "Selecciona un odontólogo activo y una acción para la cita.";
    }
}

// 2. Obtener citas pendientes y confirmadas.
try {
    $query = "SELECT c.id, c.fecha, c.hora, p.nombre AS paciente_nombre, p.cedula, 
                     c.estado::text AS estado,
                     o.id AS odontologo_id, o.nombre AS odontologo_nombre,
                     o.especialidad AS odontologo_especialidad
              FROM cita c 
              JOIN paciente p ON c.paciente_id = p.id 
              LEFT JOIN odontologo o ON c.odontologo_id = o.id 
              WHERE c.estado IN ('pendiente', 'confirmada')
              ORDER BY c.fecha DESC, c.hora DESC";
    $stmt_citas = $conexion->prepare($query);
    $stmt_citas->execute();
    $citas = $stmt_citas->fetchAll();
    $citas_pendientes = array_values(array_filter(
        $citas,
        static fn($cita) => $cita['estado'] === 'pendiente'
    ));
    $citas_confirmadas = array_values(array_filter(
        $citas,
        static fn($cita) => $cita['estado'] === 'confirmada'
    ));
} catch (PDOException $e) {
    error_log("Error al cargar citas pendientes y confirmadas: " . $e->getMessage());
    $citas_pendientes = [];
    $citas_confirmadas = [];
    $error = "No se pudieron cargar las citas.";
}

// 3. Obtener odontólogos activos para el menú desplegable
try {
    $stmt_odonto = $conexion->prepare("SELECT id, nombre, especialidad FROM odontologo WHERE activo = TRUE ORDER BY nombre");
    $stmt_odonto->execute();
    $odontologos_activos = $stmt_odonto->fetchAll();
} catch (PDOException $e) {
    error_log("Error al cargar odontólogos activos: " . $e->getMessage());
    $odontologos_activos = [];
    $error = "No se pudieron cargar los odontólogos activos.";
}
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
    <link rel="stylesheet" href="../../assests/css/admin.css">
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
                <a href="../recepcionista/cerrar_sesion.php" class="side-link"><span class="side-link__icon"><i class="fa-solid fa-right-from-bracket"></i></span>Cerrar sesión</a>
            </nav>
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
                        <i class="fa-solid fa-circle-check me-2"></i>                         <?php echo htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger py-2 small mb-3 border-0 shadow-sm">
                        <i class="fa-solid fa-triangle-exclamation me-2"></i>                         <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                <?php endif; ?>

                <div class="panel">
                    <div class="panel__head">
                        <div>
                            <h3 class="panel__title"><i class="fa-solid fa-calendar-check"></i> Catálogo de Citas</h3>
                            <p class="panel__subtitle">Gestión y asignación de odontólogos especialistas para las citas solicitadas.</p>
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
                                <?php if (count($citas_pendientes) > 0): ?>
                                    <?php foreach ($citas_pendientes as $index => $cita): ?>
                                        <tr>
                                            <td data-label="#"><span class="row-id"><?php echo $index + 1; ?></span></td>
                                            <td data-label="Paciente">
                                                <strong><?php echo htmlspecialchars($cita['paciente_nombre'], ENT_QUOTES, 'UTF-8'); ?></strong>
                                                <div class="text-muted small"><i class="fa-solid fa-id-card me-1"></i> Cédula: <?php echo htmlspecialchars((string) $cita['cedula'], ENT_QUOTES, 'UTF-8'); ?></div>
                                            </td>
                                            <td data-label="Fecha y Hora">
                                                <div><i class="fa-solid fa-calendar-days text-teal me-1"></i> <strong><?php echo $cita['fecha']; ?></strong></div>
                                                <div class="text-muted small"><i class="fa-solid fa-clock me-1"></i> <?php echo $cita['hora']; ?></div>
                                            </td>
                                            <td data-label="Odontólogo">
                                                <form action="lista_citas.php" method="POST" id="form-cita-<?php echo $cita['id']; ?>">
                                                    <input type="hidden" name="cita_id" value="<?php echo $cita['id']; ?>">
                                                    
                                                    <select name="odontologo_id" class="form-select form-select-sm" required style="border-radius: 8px;">
                                                        <option value="">-- Seleccionar --</option>
                                                        <?php foreach ($odontologos_activos as $odonto): ?>
                                                            <option value="<?php echo $odonto['id']; ?>" <?php echo ((string) $cita['odontologo_id'] === (string) $odonto['id']) ? 'selected' : ''; ?>>
                                                                <?php echo htmlspecialchars($odonto['nombre'], ENT_QUOTES, 'UTF-8'); ?> (<?php echo htmlspecialchars($odonto['especialidad'], ENT_QUOTES, 'UTF-8'); ?>)
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </form>
                                            </td>
                                            <td data-label="Acciones" class="text-center">
                                                <div class="d-flex justify-content-center gap-2 flex-wrap">
                                                    <button type="submit" form="form-cita-<?php echo $cita['id']; ?>" name="accion_cita" value="asignar" class="btn btn--primary btn--sm">
                                                        <i class="fa-solid fa-check"></i> Asignar
                                                    </button>
                                                    <button type="submit" form="form-cita-<?php echo $cita['id']; ?>" name="accion_cita" value="confirmar" class="btn btn--primary btn--sm" style="background-color: #198754;">
                                                        <i class="fa-solid fa-calendar-check"></i> Confirmar
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">No hay citas pendientes por asignar o confirmar.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="panel mt-4">
                    <div class="panel__head">
                        <div>
                            <h3 class="panel__title"><i class="fa-solid fa-circle-check"></i> Citas Confirmadas</h3>
                            <p class="panel__subtitle">Citas aceptadas por recepción y asignadas a un odontólogo.</p>
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
                                    <th class="text-center">Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($citas_confirmadas) > 0): ?>
                                    <?php foreach ($citas_confirmadas as $index => $cita): ?>
                                        <tr>
                                            <td data-label="#"><span class="row-id"><?php echo $index + 1; ?></span></td>
                                            <td data-label="Paciente">
                                                <strong><?php echo htmlspecialchars($cita['paciente_nombre'], ENT_QUOTES, 'UTF-8'); ?></strong>
                                                <div class="text-muted small"><i class="fa-solid fa-id-card me-1"></i> Cédula: <?php echo htmlspecialchars((string) $cita['cedula'], ENT_QUOTES, 'UTF-8'); ?></div>
                                            </td>
                                            <td data-label="Fecha y Hora">
                                                <div><i class="fa-solid fa-calendar-days text-teal me-1"></i> <strong><?php echo htmlspecialchars($cita['fecha'], ENT_QUOTES, 'UTF-8'); ?></strong></div>
                                                <div class="text-muted small"><i class="fa-solid fa-clock me-1"></i> <?php echo htmlspecialchars($cita['hora'], ENT_QUOTES, 'UTF-8'); ?></div>
                                            </td>
                                            <td data-label="Odontólogo">
                                                <?php if ($cita['odontologo_nombre'] !== null): ?>
                                                    <strong><?php echo htmlspecialchars($cita['odontologo_nombre'], ENT_QUOTES, 'UTF-8'); ?></strong>
                                                    <div class="text-muted small"><?php echo htmlspecialchars($cita['odontologo_especialidad'], ENT_QUOTES, 'UTF-8'); ?></div>
                                                <?php else: ?>
                                                    <span class="text-muted">Sin odontólogo asignado</span>
                                                <?php endif; ?>
                                            </td>
                                            <td data-label="Estado" class="text-center">
                                                <span class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill fw-bold">
                                                    <i class="fa-solid fa-circle-check me-1"></i> Confirmada
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">Todavía no hay citas confirmadas.</td>
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