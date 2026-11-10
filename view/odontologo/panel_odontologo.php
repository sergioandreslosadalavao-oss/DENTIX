<?php
require_once __DIR__ . '/../../app/odontologo_auth.php';
requerir_odontologo('login_odontologo.php');
require_once __DIR__ . '/../../config/database.php';

$odontologoId = (int) $_SESSION['odontologo_id'];
$nombreOdontologo = $_SESSION['odontologo_nombre'] ?? 'Especialista';
$especialidadOdontologo = $_SESSION['odontologo_especialidad'] ?? 'Odontología';
$consultorioOdontologo = $_SESSION['odontologo_consultorio'] ?? 'Consultorio';
$inicialOdontologo = mb_strtoupper(mb_substr(preg_replace('/^(Dra?\.\s+)/i', '', $nombreOdontologo), 0, 1, 'UTF-8'), 'UTF-8');

$mensaje = '';
$error = '';

// Acción: Marcar cita confirmada como FINALIZADA y guardar el detalle clínico en detalle_cita
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['finalizar_cita'])) {
    $citaId = filter_input(INPUT_POST, 'cita_id', FILTER_VALIDATE_INT);
    $detalle = trim($_POST['detalle'] ?? '');
    $procedimiento = trim($_POST['procedimiento'] ?? '');
    $notaClinica = trim($_POST['nota_clinica'] ?? '');
    $fechaSalida = trim($_POST['fecha_salida'] ?? '');
    $horaSalida = trim($_POST['hora_salida'] ?? '');

    if (!validar_csrf_odontologo()) {
        $error = 'La sesión del formulario venció. Recarga la página e inténtalo de nuevo.';
    } elseif ($citaId === false || $citaId <= 0) {
        $error = 'ID de cita no válido.';
    } elseif ($detalle === '' || $procedimiento === '' || $fechaSalida === '' || $horaSalida === '') {
        $error = 'Por favor completa la fecha y hora de salida, el resumen de la cita y el procedimiento realizado.';
    } else {
        try {
            $conexion->beginTransaction();

            $stmt = $conexion->prepare(
                "UPDATE cita
                 SET estado = 'completada'
                 WHERE id = ?
                   AND odontologo_id = ?
                   AND estado = 'confirmada'
                 RETURNING servicio_id"
            );
            $stmt->execute([$citaId, $odontologoId]);
            $servicioId = $stmt->fetchColumn();

            if ($servicioId) {
                $stmtDetalle = $conexion->prepare(
                    "INSERT INTO detalle_cita (cita_id, servicio_id, detalle, procedimiento, nota_clinica, fecha_salida, hora_salida)
                     VALUES (?, ?, ?, ?, ?, ?, ?)"
                );
                $stmtDetalle->execute([
                    $citaId,
                    $servicioId,
                    $detalle,
                    $procedimiento,
                    $notaClinica !== '' ? $notaClinica : 'Sin novedades o problemáticas reportadas.',
                    $fechaSalida,
                    $horaSalida
                ]);

                $conexion->commit();
                $mensaje = '¡Cita marcada como FINALIZADA y detalle clínico con hora de salida registrado!';
            } else {
                $conexion->rollBack();
                $error = 'No se pudo finalizar la cita. Verifica que siga en estado confirmada y te pertenezca.';
            }
        } catch (PDOException $e) {
            if ($conexion->inTransaction()) {
                $conexion->rollBack();
            }
            error_log('Error al finalizar cita del odontólogo: ' . $e->getMessage());
            $error = 'Ocurrió un error al guardar la finalización y detalle clínico en la base de datos.';
        }
    }
}

// Consultar citas activas (confirmadas) asignadas a este odontólogo
try {
    $stmtActivas = $conexion->prepare(
        "SELECT c.id, c.fecha, c.hora, c.motivo,
                p.nombre AS paciente_nombre, p.cedula AS paciente_cedula, p.telefono AS paciente_telefono, p.alergias AS paciente_alergias,
                s.nombre AS servicio_nombre, s.duracion_min
         FROM cita c
         INNER JOIN paciente p ON p.id = c.paciente_id
         INNER JOIN servicio s ON s.id = c.servicio_id
         WHERE c.odontologo_id = ?
           AND c.estado = 'confirmada'
         ORDER BY c.fecha ASC, c.hora ASC"
    );
    $stmtActivas->execute([$odontologoId]);
    $citasActivas = $stmtActivas->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $citasActivas = [];
    error_log('Error al cargar citas activas del odontólogo: ' . $e->getMessage());
}

// Consultar historial completo de citas finalizadas (completadas) de este odontólogo en la nube
try {
    $stmtFinalizadas = $conexion->prepare(
        "SELECT c.id, c.fecha, c.hora,
                p.nombre AS paciente_nombre, p.cedula AS paciente_cedula,
                s.nombre AS servicio_nombre,
                dc.detalle, dc.procedimiento, dc.nota_clinica, dc.fecha_salida, dc.hora_salida, dc.creado_en AS detalle_fecha
         FROM cita c
         INNER JOIN paciente p ON p.id = c.paciente_id
         INNER JOIN servicio s ON s.id = c.servicio_id
         LEFT JOIN detalle_cita dc ON dc.cita_id = c.id
         WHERE c.odontologo_id = ?
           AND c.estado = 'completada'
         ORDER BY COALESCE(dc.creado_en, c.creado_en) DESC, c.fecha DESC, c.hora DESC"
    );
    $stmtFinalizadas->execute([$odontologoId]);
    $citasFinalizadas = $stmtFinalizadas->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $citasFinalizadas = [];
    error_log('Error al cargar citas finalizadas del odontólogo: ' . $e->getMessage());
}

// Filtro de búsqueda en vivo en el historial si el odontólogo escribe un nombre, cédula o tratamiento
$busquedaHistorial = trim($_GET['buscar_historial'] ?? '');
if ($busquedaHistorial !== '') {
    $citasFinalizadasFiltradas = array_values(array_filter($citasFinalizadas, function ($item) use ($busquedaHistorial) {
        $needle = mb_strtolower($busquedaHistorial, 'UTF-8');
        return str_contains(mb_strtolower($item['paciente_nombre'] ?? '', 'UTF-8'), $needle)
            || str_contains(mb_strtolower($item['paciente_cedula'] ?? '', 'UTF-8'), $needle)
            || str_contains(mb_strtolower($item['servicio_nombre'] ?? '', 'UTF-8'), $needle);
    }));
} else {
    $citasFinalizadasFiltradas = $citasFinalizadas;
}

$csrfToken = token_csrf_odontologo();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Agenda Médica - DENTIX</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@500;600;700;800&family=Sora:wght@700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="../../assests/css/admin.css?v=20261103-2">
    <link rel="stylesheet" href="../../assests/css/index.css">
</head>
<body class="admin-body" data-accent="odontologos">
    <div class="page-progress"><span class="page-progress__bar"></span></div>
    <div class="app-shell">

        <!-- BARRA LATERAL DEL ODONTÓLOGO -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar__top">
                <a href="../../index.php" class="brand">
                    <div class="brand__mark"><i class="fa-solid fa-tooth"></i></div>
                    <div class="brand__text">DENTIX<small>Portal Médico</small></div>
                </a>
                <button class="sidebar__close" id="sidebar-close"><svg viewBox="0 0 24 24"><path d="M18 6L6 18M6 6l12 12"/></svg></button>
            </div>
            <nav class="sidebar__nav">
                <span class="sidebar__label">Mi Consulta</span>
                <a href="panel_odontologo.php" class="side-link is-active">
                    <span class="side-link__icon"><i class="fa-solid fa-calendar-check"></i></span>Mi Agenda
                </a>
                <a href="historial_finalizadas.php" class="side-link">
                    <span class="side-link__icon"><i class="fa-solid fa-clipboard-check"></i></span>Historial Finalizadas
                </a>
                <a href="../../index.php" class="side-link">
                    <span class="side-link__icon"><i class="fa-solid fa-house"></i></span>Ir al Inicio
                </a>
            </nav>

            <!-- TARJETA DE PERFIL DEL ODONTÓLOGO Y CIERRE DE SESIÓN -->
            <div class="sidebar__footer">
                <div class="sidebar-user-card" style="
                    display: flex;
                    align-items: center;
                    gap: 12px;
                    padding: 12px 14px;
                    margin-bottom: 14px;
                    border-radius: 16px;
                    background: linear-gradient(135deg, rgba(255, 255, 255, 0.1) 0%, rgba(255, 255, 255, 0.04) 100%);
                    border: 1px solid rgba(255, 255, 255, 0.16);
                    backdrop-filter: blur(10px);
                    box-shadow: 0 4px 16px -2px rgba(0, 0, 0, 0.25);
                ">
                    <div style="
                        position: relative;
                        width: 40px;
                        height: 40px;
                        flex-shrink: 0;
                        border-radius: 12px;
                        background: linear-gradient(135deg, #10b981 0%, #0d9488 100%);
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        color: #ffffff;
                        font-weight: 800;
                        font-size: 1rem;
                        font-family: 'Sora', sans-serif;
                        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4);
                        border: 1px solid rgba(255, 255, 255, 0.3);
                    ">
                        <?php echo htmlspecialchars($inicialOdontologo, ENT_QUOTES, 'UTF-8'); ?>
                        <span style="
                            position: absolute;
                            bottom: -2px;
                            right: -2px;
                            width: 10px;
                            height: 10px;
                            border-radius: 50%;
                            background: #10b981;
                            border: 2px solid #064e3b;
                            box-shadow: 0 0 6px #10b981;
                        "></span>
                    </div>

                    <div style="min-width: 0; flex: 1; line-height: 1.2;">
                        <div style="
                            font-family: 'Manrope', sans-serif;
                            font-weight: 700;
                            font-size: 0.85rem;
                            color: #ffffff;
                            white-space: nowrap;
                            overflow: hidden;
                            text-overflow: ellipsis;
                        ">
                            <?php echo htmlspecialchars($nombreOdontologo, ENT_QUOTES, 'UTF-8'); ?>
                        </div>
                        <div style="
                            display: flex;
                            align-items: center;
                            gap: 5px;
                            margin-top: 3px;
                            font-size: 0.7rem;
                            font-weight: 600;
                            color: #a7f3d0;
                            white-space: nowrap;
                            overflow: hidden;
                            text-overflow: ellipsis;
                        ">
                            <i class="fa-solid fa-stethoscope" style="font-size: 0.65rem;"></i>
                            <span><?php echo htmlspecialchars($especialidadOdontologo, ENT_QUOTES, 'UTF-8'); ?></span>
                        </div>
                    </div>
                </div>

                <form action="cerrar_sesion_odontologo.php" method="POST" style="margin: 0;">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
                    <button class="sidebar__logout" type="submit">
                        <span class="sidebar__logout-icon" aria-hidden="true">
                            <i class="fa-solid fa-arrow-right-from-bracket"></i>
                        </span>
                        <span class="sidebar__logout-copy">
                            <strong>Cerrar sesión</strong>
                            <small>Salir de forma segura</small>
                        </span>
                        <span class="sidebar__logout-arrow" aria-hidden="true">
                            <i class="fa-solid fa-arrow-right"></i>
                        </span>
                    </button>
                </form>
            </div>
        </aside>

        <!-- CONTENIDO PRINCIPAL -->
        <main class="app-main">
            <header class="topbar">
                <button class="menu-btn" id="menu-toggle"><span></span><span></span><span></span></button>
                <div class="topbar__crumbs">
                    <a href="../../index.php">Inicio</a><span>/</span><strong>Agenda de <?php echo htmlspecialchars($nombreOdontologo, ENT_QUOTES, 'UTF-8'); ?></strong>
                </div>
                <div class="topbar__actions">
                    <span class="badge bg-light text-dark border px-3 py-2 rounded-pill fw-semibold" style="font-size: 0.78rem;">
                        <i class="fa-solid fa-door-open text-teal me-1"></i> <?php echo htmlspecialchars($consultorioOdontologo, ENT_QUOTES, 'UTF-8'); ?>
                    </span>
                </div>
            </header>

            <div class="page">

                <?php if ($mensaje !== ''): ?>
                    <div class="alert alert-success py-2 small mb-3 border-0 shadow-sm d-flex align-items-center">
                        <i class="fa-solid fa-circle-check me-2 fs-5"></i>
                        <div><?php echo htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8'); ?></div>
                    </div>
                <?php endif; ?>

                <?php if ($error !== ''): ?>
                    <div class="alert alert-danger py-2 small mb-3 border-0 shadow-sm d-flex align-items-center">
                        <i class="fa-solid fa-triangle-exclamation me-2 fs-5"></i>
                        <div><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
                    </div>
                <?php endif; ?>

                <!-- TARJETAS RESUMEN DEL ODONTÓLOGO -->
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <div class="panel p-3 d-flex align-items-center gap-3">
                            <div class="rounded-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background: #ecfdf5; color: #059669; font-size: 1.3rem;">
                                <i class="fa-solid fa-calendar-day"></i>
                            </div>
                            <div>
                                <div class="text-muted small fw-semibold">Citas por Atender</div>
                                <div class="fs-4 fw-bold text-dark"><?php echo count($citasActivas); ?></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="panel p-3 d-flex align-items-center gap-3">
                            <div class="rounded-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background: #f0f9ff; color: #0284c7; font-size: 1.3rem;">
                                <i class="fa-solid fa-check-double"></i>
                            </div>
                            <div>
                                <div class="text-muted small fw-semibold">Citas Finalizadas</div>
                                <div class="fs-4 fw-bold text-dark"><?php echo count($citasFinalizadas); ?></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="panel p-3 d-flex align-items-center gap-3">
                            <?php $ocupado = count($citasActivas) > 0; ?>
                            <div class="rounded-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background: <?php echo $ocupado ? '#fef2f2' : '#ecfdf5'; ?>; color: <?php echo $ocupado ? '#dc2626' : '#059669'; ?>; font-size: 1.3rem;">
                                <i class="fa-solid <?php echo $ocupado ? 'fa-clock' : 'fa-circle-check'; ?>"></i>
                            </div>
                            <div>
                                <div class="text-muted small fw-semibold">Mi Estado Actual</div>
                                <div class="fs-6 fw-bold <?php echo $ocupado ? 'text-danger' : 'text-success'; ?>">
                                    <?php echo $ocupado ? 'Ocupado / En Cita' : 'Activo / Disponible'; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TABLA 1: CITAS ASIGNADAS (PENDIENTES POR FINALIZAR) -->
                <div class="panel">
                    <div class="panel__head">
                        <div>
                            <h3 class="panel__title"><i class="fa-solid fa-stethoscope"></i> Mis Citas Asignadas</h3>
                            <p class="panel__subtitle">Pacientes confirmados por recepción bajo tu atención. Presiona <strong>Finalizada</strong> al concluir la consulta.</p>
                        </div>
                        <span class="row-id"><?php echo count($citasActivas); ?></span>
                    </div>

                    <div class="table-wrap">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Paciente / Cédula</th>
                                    <th>Tratamiento</th>
                                    <th>Alergias</th>
                                    <th>Fecha y Hora</th>
                                    <th>Estado</th>
                                    <th class="text-center">Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($citasActivas !== []): ?>
                                    <?php foreach ($citasActivas as $index => $cita): ?>
                                        <tr>
                                            <td data-label="#"><span class="row-id"><?php echo $index + 1; ?></span></td>
                                            <td data-label="Paciente">
                                                <strong><?php echo htmlspecialchars($cita['paciente_nombre'], ENT_QUOTES, 'UTF-8'); ?></strong>
                                                <div class="text-muted small"><i class="fa-solid fa-id-card me-1"></i> Cédula: <?php echo htmlspecialchars($cita['paciente_cedula'], ENT_QUOTES, 'UTF-8'); ?></div>
                                                <div class="text-muted small"><i class="fa-solid fa-phone me-1"></i> <?php echo htmlspecialchars($cita['paciente_telefono'], ENT_QUOTES, 'UTF-8'); ?></div>
                                            </td>
                                            <td data-label="Tratamiento">
                                                <span class="chip chip--accent"><?php echo htmlspecialchars($cita['servicio_nombre'], ENT_QUOTES, 'UTF-8'); ?></span>
                                                <div class="text-muted small mt-1"><i class="fa-regular fa-hourglass-half me-1"></i> <?php echo (int) $cita['duracion_min']; ?> min</div>
                                            </td>
                                            <td data-label="Alergias">
                                                <?php if (!empty($cita['paciente_alergias']) && strtolower($cita['paciente_alergias']) !== 'ninguna'): ?>
                                                    <span class="badge bg-danger bg-opacity-10 text-danger px-2 py-1 rounded-pill small fw-bold">
                                                        <i class="fa-solid fa-triangle-exclamation me-1"></i> <?php echo htmlspecialchars($cita['paciente_alergias'], ENT_QUOTES, 'UTF-8'); ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="text-muted small">Sin alergias</span>
                                                <?php endif; ?>
                                            </td>
                                            <td data-label="Fecha y Hora">
                                                <div><i class="fa-solid fa-calendar-days text-teal me-1"></i> <strong><?php echo htmlspecialchars($cita['fecha'], ENT_QUOTES, 'UTF-8'); ?></strong></div>
                                                <div class="text-muted small"><i class="fa-solid fa-clock me-1"></i> <?php echo htmlspecialchars(substr((string) $cita['hora'], 0, 5), ENT_QUOTES, 'UTF-8'); ?></div>
                                            </td>
                                            <td data-label="Estado">
                                                <span class="badge bg-warning-subtle text-warning-emphasis px-3 py-2 rounded-pill fw-bold" style="font-size: 0.75rem;">
                                                    <i class="fa-solid fa-circle-dot me-1"></i> Confirmada
                                                </span>
                                            </td>
                                            <td data-label="Acción" class="text-center">
                                                <button type="button"
                                                        class="btn btn--primary btn--sm"
                                                        style="display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px;"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#modalFinalizarCita"
                                                        data-cita-id="<?php echo (int) $cita['id']; ?>"
                                                        data-paciente="<?php echo htmlspecialchars($cita['paciente_nombre'], ENT_QUOTES, 'UTF-8'); ?>"
                                                        data-servicio="<?php echo htmlspecialchars($cita['servicio_nombre'], ENT_QUOTES, 'UTF-8'); ?>"
                                                        data-fecha-raw="<?php echo htmlspecialchars($cita['fecha'], ENT_QUOTES, 'UTF-8'); ?>"
                                                        data-fecha="<?php echo htmlspecialchars($cita['fecha'] . ' ' . substr((string) $cita['hora'], 0, 5), ENT_QUOTES, 'UTF-8'); ?>">
                                                    <i class="fa-solid fa-check-double"></i> Finalizada
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-5">
                                            <i class="fa-solid fa-calendar-check fs-3 d-block mb-2 text-success opacity-50"></i>
                                            No tienes citas confirmadas pendientes por atender en este momento.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- TABLA 2: REPOSITORIO / NUBE HISTÓRICA DE CITAS FINALIZADAS -->
                <div class="panel mt-4" id="citas-finalizadas">
                    <div class="panel__head d-flex flex-wrap justify-content-between align-items-center gap-3">
                        <div>
                            <h3 class="panel__title"><i class="fa-solid fa-cloud-arrow-up text-teal"></i> Historial de Citas Finalizadas</h3>
                            <p class="panel__subtitle">Archivo clínico permanente de todas tus atenciones finalizadas, detalles y salidas registradas.</p>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <form action="panel_odontologo.php#citas-finalizadas" method="GET" class="d-flex gap-2 align-items-center" style="margin: 0;">
                                <div class="input-group input-group-sm" style="width: 240px;">
                                    <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                                    <input type="text" name="buscar_historial" class="form-control border-start-0" placeholder="Buscar paciente o cédula..." value="<?php echo htmlspecialchars($busquedaHistorial, ENT_QUOTES, 'UTF-8'); ?>">
                                </div>
                                <?php if ($busquedaHistorial !== ''): ?>
                                    <a href="panel_odontologo.php#citas-finalizadas" class="btn btn-light btn-sm border" title="Limpiar búsqueda"><i class="fa-solid fa-xmark"></i></a>
                                <?php endif; ?>
                            </form>
                            <span class="row-id" title="Total finalizadas"><?php echo count($citasFinalizadas); ?></span>
                        </div>
                    </div>

                    <div class="table-wrap">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Paciente / Cédula</th>
                                    <th>Tratamiento / Tiempos</th>
                                    <th>Detalle de la Cita y Procedimiento</th>
                                    <th>Novedades / Nota Clínica</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($citasFinalizadasFiltradas !== []): ?>
                                    <?php foreach ($citasFinalizadasFiltradas as $index => $cita): ?>
                                        <tr>
                                            <td data-label="#"><span class="row-id"><?php echo $index + 1; ?></span></td>
                                            <td data-label="Paciente">
                                                <strong><?php echo htmlspecialchars($cita['paciente_nombre'], ENT_QUOTES, 'UTF-8'); ?></strong>
                                                <div class="text-muted small"><i class="fa-solid fa-id-card me-1"></i> Cédula: <?php echo htmlspecialchars($cita['paciente_cedula'], ENT_QUOTES, 'UTF-8'); ?></div>
                                            </td>
                                            <td data-label="Tratamiento">
                                                <span class="chip chip--accent mb-1 d-inline-block"><?php echo htmlspecialchars($cita['servicio_nombre'], ENT_QUOTES, 'UTF-8'); ?></span>
                                                <div class="text-muted small">
                                                    <i class="fa-solid fa-calendar-days text-teal me-1"></i> <strong>Ingreso:</strong> <?php echo htmlspecialchars($cita['fecha'], ENT_QUOTES, 'UTF-8'); ?> &middot; <?php echo htmlspecialchars(substr((string) $cita['hora'], 0, 5), ENT_QUOTES, 'UTF-8'); ?>
                                                </div>
                                                <?php if (!empty($cita['fecha_salida']) || !empty($cita['hora_salida'])): ?>
                                                    <div class="text-muted small">
                                                        <i class="fa-solid fa-clock-rotate-left text-primary me-1"></i> <strong>Salida:</strong> <?php echo htmlspecialchars($cita['fecha_salida'] ?? '', ENT_QUOTES, 'UTF-8'); ?> &middot; <?php echo htmlspecialchars(substr((string) ($cita['hora_salida'] ?? ''), 0, 5), ENT_QUOTES, 'UTF-8'); ?>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td data-label="Detalle y Procedimiento" style="max-width: 280px;">
                                                <?php if (!empty($cita['detalle']) || !empty($cita['procedimiento'])): ?>
                                                    <div class="small text-dark mb-1">
                                                        <strong><i class="fa-solid fa-file-medical text-teal me-1"></i> Resumen:</strong>
                                                        <?php echo htmlspecialchars($cita['detalle'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                                    </div>
                                                    <div class="small text-muted">
                                                        <strong><i class="fa-solid fa-kit-medical me-1"></i> Procedimiento:</strong>
                                                        <?php echo htmlspecialchars($cita['procedimiento'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                                    </div>
                                                <?php else: ?>
                                                    <span class="text-muted small fst-italic">Sin detalle clínico registrado</span>
                                                <?php endif; ?>
                                            </td>
                                            <td data-label="Novedades / Problemáticas" style="max-width: 250px;">
                                                <?php if (!empty($cita['nota_clinica'])): ?>
                                                    <div class="small p-2 rounded-3" style="background: #f8fafc; border: 1px solid #e2e8f0;">
                                                        <i class="fa-solid fa-notes-medical text-primary me-1"></i>
                                                        <?php echo htmlspecialchars($cita['nota_clinica'], ENT_QUOTES, 'UTF-8'); ?>
                                                    </div>
                                                <?php else: ?>
                                                    <span class="text-muted small">Sin novedades</span>
                                                <?php endif; ?>
                                            </td>
                                            <td data-label="Estado">
                                                <span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill fw-bold" style="font-size: 0.75rem;">
                                                    <i class="fa-solid fa-check-double me-1"></i> Finalizada
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">
                                            <?php echo $busquedaHistorial !== '' ? 'No se encontraron citas finalizadas con ese criterio.' : 'Todavía no has finalizado ninguna cita.'; ?>
                                        </td>
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

    <!-- MODAL PARA REGISTRAR DETALLE_CITA AL FINALIZAR -->
    <div class="modal fade" id="modalFinalizarCita" tabindex="-1" aria-labelledby="modalFinalizarCitaLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="modal-header border-0 px-4 pt-4 pb-3" style="background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%); color: white;">
                    <div>
                        <span class="badge bg-white bg-opacity-25 text-white mb-1" style="font-size: 0.7rem; letter-spacing: 0.05em;">REGISTRO CLÍNICO (DETALLE_CITA)</span>
                        <h5 class="modal-title fw-bold" id="modalFinalizarCitaLabel" style="font-family: 'Sora', sans-serif;">
                            <i class="fa-solid fa-file-waveform me-2"></i> Finalizar Cita y Reportar Detalle
                        </h5>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <form action="panel_odontologo.php" method="POST">
                    <div class="modal-body p-4">
                        <input type="hidden" name="finalizar_cita" value="1">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
                        <input type="hidden" name="cita_id" id="modal_cita_id" value="">

                        <div class="p-3 mb-4 rounded-3 d-flex flex-wrap gap-3 justify-content-between align-items-center" style="background: #f0fdf4; border: 1px solid #bbf7d0;">
                            <div>
                                <div class="small text-muted fw-semibold">Paciente</div>
                                <strong class="text-dark" id="modal_paciente_nombre">-</strong>
                            </div>
                            <div>
                                <div class="small text-muted fw-semibold">Tratamiento</div>
                                <strong class="text-teal" id="modal_servicio_nombre">-</strong>
                            </div>
                            <div>
                                <div class="small text-muted fw-semibold">Fecha y Hora de Ingreso</div>
                                <strong class="text-dark" id="modal_fecha_hora">-</strong>
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label for="fecha_salida" class="form-label fw-bold small text-dark">
                                    <i class="fa-solid fa-calendar-check text-teal me-1"></i> Fecha de Salida de la Cita <span class="text-danger">*</span>
                                </label>
                                <input type="date" class="form-control rounded-3" id="fecha_salida" name="fecha_salida" required>
                            </div>
                            <div class="col-md-6">
                                <label for="hora_salida" class="form-label fw-bold small text-dark">
                                    <i class="fa-solid fa-clock text-teal me-1"></i> Hora de Salida de la Cita <span class="text-danger">*</span>
                                </label>
                                <input type="time" class="form-control rounded-3" id="hora_salida" name="hora_salida" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="detalle" class="form-label fw-bold small text-dark">
                                <i class="fa-solid fa-comment-medical text-teal me-1"></i> Resumen breve de cómo fue la cita <span class="text-danger">*</span>
                            </label>
                            <textarea class="form-control rounded-3" id="detalle" name="detalle" rows="2" required placeholder="Ej. El paciente asistió puntualmente, presentó buena tolerancia durante la sesión..."></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="procedimiento" class="form-label fw-bold small text-dark">
                                <i class="fa-solid fa-tooth text-teal me-1"></i> Procedimiento realizado <span class="text-danger">*</span>
                            </label>
                            <textarea class="form-control rounded-3" id="procedimiento" name="procedimiento" rows="2" required placeholder="Ej. Profilaxis ultrasónica, aplicación de flúor, ajuste de brackets, resina en pieza 16..."></textarea>
                        </div>

                        <div class="mb-2">
                            <label for="nota_clinica" class="form-label fw-bold small text-dark">
                                <i class="fa-solid fa-triangle-exclamation text-warning me-1"></i> Extras, recomendaciones o problemáticas presentadas
                            </label>
                            <textarea class="form-control rounded-3" id="nota_clinica" name="nota_clinica" rows="2" placeholder="Ej. Sensibilidad leve en molar inferior, se recomienda control en 15 días. (Si no hubo inconvenientes puedes dejarlo vacío)"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer border-0 px-4 pb-4 pt-0 d-flex justify-content-between">
                        <button type="button" class="btn btn-light rounded-3 px-4 fw-semibold text-muted" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn--primary rounded-3 px-4 fw-bold shadow-sm">
                            <i class="fa-solid fa-check-double me-2"></i> Guardar y Finalizar Cita
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
    <script src="../../assests/js/admin-animations.js"></script>
    <script>
        const modalFinalizar = document.getElementById('modalFinalizarCita');
        if (modalFinalizar) {
            modalFinalizar.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                document.getElementById('modal_cita_id').value = button.getAttribute('data-cita-id');
                document.getElementById('modal_paciente_nombre').textContent = button.getAttribute('data-paciente');
                document.getElementById('modal_servicio_nombre').textContent = button.getAttribute('data-servicio');
                document.getElementById('modal_fecha_hora').textContent = button.getAttribute('data-fecha');
                document.getElementById('fecha_salida').value = button.getAttribute('data-fecha-raw') || '';
                const now = new Date();
                document.getElementById('hora_salida').value = now.toTimeString().slice(0, 5);
            });
        }
    </script>
</body>
</html>

