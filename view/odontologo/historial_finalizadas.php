<?php
require_once __DIR__ . '/../../app/odontologo_auth.php';
requerir_odontologo('login_odontologo.php');
require_once __DIR__ . '/../../config/database.php';

$odontologoId = (int) $_SESSION['odontologo_id'];
$nombreOdontologo = $_SESSION['odontologo_nombre'] ?? 'Especialista';
$especialidadOdontologo = $_SESSION['odontologo_especialidad'] ?? 'Odontología';
$consultorioOdontologo = $_SESSION['odontologo_consultorio'] ?? 'Consultorio';
$inicialOdontologo = mb_strtoupper(mb_substr(preg_replace('/^(Dra?\.\s+)/i', '', $nombreOdontologo), 0, 1, 'UTF-8'), 'UTF-8');

// Consultar TODAS las citas finalizadas (completadas) de este odontólogo en la nube
$busquedaHistorial = trim($_GET['buscar'] ?? '');

try {
    $stmtFinalizadas = $conexion->prepare(
        "SELECT c.id, c.fecha, c.hora,
                p.nombre AS paciente_nombre, p.cedula AS paciente_cedula, p.telefono AS paciente_telefono, p.alergias AS paciente_alergias,
                s.nombre AS servicio_nombre, s.duracion_min,
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
    error_log('Error al cargar nube de historial de citas: ' . $e->getMessage());
}

if ($busquedaHistorial !== '') {
    $citasFiltradas = array_values(array_filter($citasFinalizadas, function ($item) use ($busquedaHistorial) {
        $needle = mb_strtolower($busquedaHistorial, 'UTF-8');
        return str_contains(mb_strtolower($item['paciente_nombre'] ?? '', 'UTF-8'), $needle)
            || str_contains(mb_strtolower($item['paciente_cedula'] ?? '', 'UTF-8'), $needle)
            || str_contains(mb_strtolower($item['servicio_nombre'] ?? '', 'UTF-8'), $needle)
            || str_contains(mb_strtolower($item['procedimiento'] ?? '', 'UTF-8'), $needle);
    }));
} else {
    $citasFiltradas = $citasFinalizadas;
}

$csrfToken = token_csrf_odontologo();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de Citas Finalizadas - DENTIX</title>
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
                <a href="panel_odontologo.php" class="side-link">
                    <span class="side-link__icon"><i class="fa-solid fa-calendar-check"></i></span>Mi Agenda
                </a>
                <a href="historial_finalizadas.php" class="side-link is-active">
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
                    <a href="panel_odontologo.php">Mi Agenda</a><span>/</span><strong>Historial de Citas Finalizadas</strong>
                </div>
                <div class="topbar__actions">
                    <span class="badge bg-light text-dark border px-3 py-2 rounded-pill fw-semibold" style="font-size: 0.78rem;">
                        <i class="fa-solid fa-cloud text-teal me-1"></i> Archivo Histórico Personal
                    </span>
                </div>
            </header>

            <div class="page">

                <div class="panel">
                    <div class="panel__head d-flex flex-wrap justify-content-between align-items-center gap-3">
                        <div>
                            <h3 class="panel__title"><i class="fa-solid fa-cloud-arrow-up text-teal"></i> Nube de Historial — Citas Finalizadas</h3>
                            <p class="panel__subtitle">Todas las atenciones completadas bajo el perfil de <strong><?php echo htmlspecialchars($nombreOdontologo, ENT_QUOTES, 'UTF-8'); ?></strong>, con su detalle clínico y horarios de ingreso/salida.</p>
                        </div>

                        <div class="d-flex align-items-center gap-2">
                            <form action="historial_finalizadas.php" method="GET" class="d-flex gap-2 align-items-center" style="margin: 0;">
                                <div class="input-group input-group-sm" style="width: 260px;">
                                    <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                                    <input type="text" name="buscar" class="form-control border-start-0" placeholder="Buscar paciente, cédula o servicio..." value="<?php echo htmlspecialchars($busquedaHistorial, ENT_QUOTES, 'UTF-8'); ?>">
                                </div>
                                <?php if ($busquedaHistorial !== ''): ?>
                                    <a href="historial_finalizadas.php" class="btn btn-light btn-sm border" title="Limpiar búsqueda"><i class="fa-solid fa-xmark"></i></a>
                                <?php endif; ?>
                            </form>
                            <span class="row-id" title="Total en historial"><?php echo count($citasFinalizadas); ?></span>
                        </div>
                    </div>

                    <div class="table-wrap">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Paciente / Contacto</th>
                                    <th>Tratamiento / Tiempos</th>
                                    <th>Detalle de la Cita y Procedimiento</th>
                                    <th>Novedades / Nota Clínica</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($citasFiltradas !== []): ?>
                                    <?php foreach ($citasFiltradas as $index => $cita): ?>
                                        <tr>
                                            <td data-label="#"><span class="row-id"><?php echo $index + 1; ?></span></td>
                                            <td data-label="Paciente">
                                                <strong><?php echo htmlspecialchars($cita['paciente_nombre'], ENT_QUOTES, 'UTF-8'); ?></strong>
                                                <div class="text-muted small"><i class="fa-solid fa-id-card me-1"></i> Cédula: <?php echo htmlspecialchars($cita['paciente_cedula'], ENT_QUOTES, 'UTF-8'); ?></div>
                                                <div class="text-muted small"><i class="fa-solid fa-phone me-1"></i> <?php echo htmlspecialchars($cita['paciente_telefono'], ENT_QUOTES, 'UTF-8'); ?></div>
                                            </td>
                                            <td data-label="Tratamiento y Tiempos">
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
                                            <td data-label="Novedades / Problemáticas" style="max-width: 260px;">
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
                                        <td colspan="6" class="text-center text-muted py-5">
                                            <i class="fa-solid fa-folder-open fs-3 d-block mb-2 opacity-50"></i>
                                            <?php echo $busquedaHistorial !== '' ? 'No se encontraron citas finalizadas con ese criterio.' : 'Todavía no hay citas finalizadas guardadas en tu historial.'; ?>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
    <script src="../../assests/js/admin-animations.js"></script>
    <script src="../../assests/js/dentix-i18n.js?v=20261110-cur"></script>
</body>
</html>

