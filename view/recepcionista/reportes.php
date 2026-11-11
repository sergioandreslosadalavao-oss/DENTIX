<?php
// view/recepcionista/reportes.php - Módulo de Reportes y Estadísticas Clínicas para Recepción
require_once __DIR__ . '/../../app/recepcionista_auth.php';
requerir_recepcionista('lista_recepcionista.php');
require_once __DIR__ . '/../../config/database.php';

$fechaDesde = trim($_GET['desde'] ?? '');
$fechaHasta = trim($_GET['hasta'] ?? '');

$validarFecha = static function (string $f): bool {
    if ($f === '') {
        return false;
    }
    $dt = DateTimeImmutable::createFromFormat('!Y-m-d', $f);
    return $dt !== false && $dt->format('Y-m-d') === $f;
};

if (!$validarFecha($fechaDesde)) {
    $fechaDesde = '';
}
if (!$validarFecha($fechaHasta)) {
    $fechaHasta = '';
}
if ($fechaDesde !== '' && $fechaHasta !== '' && $fechaDesde > $fechaHasta) {
    [$fechaDesde, $fechaHasta] = [$fechaHasta, $fechaDesde];
}

$condicionesCita = [];
$paramsCita = [];
if ($fechaDesde !== '') {
    $condicionesCita[] = 'c.fecha >= :desde';
    $paramsCita[':desde'] = $fechaDesde;
}
if ($fechaHasta !== '') {
    $condicionesCita[] = 'c.fecha <= :hasta';
    $paramsCita[':hasta'] = $fechaHasta;
}

$whereCita = $condicionesCita ? ('WHERE ' . implode(' AND ', $condicionesCita)) : '';
$andCita = $condicionesCita ? ('AND ' . implode(' AND ', $condicionesCita)) : '';

$resumen = [
    'total_citas' => 0,
    'pendientes' => 0,
    'confirmadas' => 0,
    'completadas' => 0,
    'canceladas' => 0,
    'ingresos_estimados' => 0,
];
$totalPacientes = 0;
$totalOdontologosActivos = 0;
$topPacientes = [];
$topServicios = [];
$topOdontologos = [];
$horariosPico = [];
$error = '';

try {
    // 1. Resumen general de citas e ingresos estimados
    $stmtResumen = $conexion->prepare(
        "SELECT
            COUNT(c.id) AS total_citas,
            COUNT(c.id) FILTER (WHERE c.estado = 'pendiente') AS pendientes,
            COUNT(c.id) FILTER (WHERE c.estado = 'confirmada') AS confirmadas,
            COUNT(c.id) FILTER (WHERE c.estado = 'completada') AS completadas,
            COUNT(c.id) FILTER (WHERE c.estado IN ('cancelada', 'no_show')) AS canceladas,
            COALESCE(SUM(s.precio) FILTER (WHERE c.estado IN ('confirmada', 'completada')), 0) AS ingresos_estimados
         FROM cita c
         INNER JOIN servicio s ON s.id = c.servicio_id
         {$whereCita}"
    );
    $stmtResumen->execute($paramsCita);
    $resumenDb = $stmtResumen->fetch(PDO::FETCH_ASSOC);
    if ($resumenDb) {
        $resumen = $resumenDb;
    }

    $totalPacientes = (int) $conexion->query('SELECT COUNT(*) FROM paciente')->fetchColumn();
    $totalOdontologosActivos = (int) $conexion->query('SELECT COUNT(*) FROM odontologo WHERE activo IS TRUE')->fetchColumn();

    // 2. Pacientes que más citas han agendado
    $stmtPacientes = $conexion->prepare(
        "SELECT p.id, p.nombre, p.cedula, p.telefono,
                COUNT(c.id) AS total_citas,
                COUNT(c.id) FILTER (WHERE c.estado = 'completada') AS completadas,
                COUNT(c.id) FILTER (WHERE c.estado = 'confirmada') AS confirmadas,
                COUNT(c.id) FILTER (WHERE c.estado = 'pendiente') AS pendientes,
                COUNT(c.id) FILTER (WHERE c.estado IN ('cancelada', 'no_show')) AS canceladas,
                MAX(c.fecha) AS ultima_cita
         FROM paciente p
         INNER JOIN cita c ON c.paciente_id = p.id
         {$whereCita}
         GROUP BY p.id, p.nombre, p.cedula, p.telefono
         ORDER BY total_citas DESC, completadas DESC, p.nombre ASC"
    );
    $stmtPacientes->execute($paramsCita);
    $topPacientes = $stmtPacientes->fetchAll(PDO::FETCH_ASSOC);

    // 3. Servicios más utilizados y pedidos
    $stmtServicios = $conexion->prepare(
        "SELECT s.id, s.nombre, s.duracion_min, s.precio,
                COUNT(c.id) AS total_solicitudes,
                COUNT(c.id) FILTER (WHERE c.estado = 'completada') AS completadas,
                COUNT(c.id) FILTER (WHERE c.estado = 'confirmada') AS confirmadas,
                COUNT(c.id) FILTER (WHERE c.estado = 'pendiente') AS pendientes,
                COALESCE(SUM(s.precio) FILTER (WHERE c.estado IN ('confirmada', 'completada')), 0) AS ingresos_estimados
         FROM servicio s
         LEFT JOIN cita c ON c.servicio_id = s.id {$andCita}
         GROUP BY s.id, s.nombre, s.duracion_min, s.precio
         ORDER BY total_solicitudes DESC, completadas DESC, s.nombre ASC"
    );
    $stmtServicios->execute($paramsCita);
    $topServicios = $stmtServicios->fetchAll(PDO::FETCH_ASSOC);

    // 4. Odontólogos que más atienden / más citas han tenido
    $stmtOdontologos = $conexion->prepare(
        "SELECT o.id, o.nombre, o.especialidad, o.consultorio, o.activo,
                COUNT(c.id) AS total_citas,
                COUNT(c.id) FILTER (WHERE c.estado = 'completada') AS finalizadas,
                COUNT(c.id) FILTER (WHERE c.estado = 'confirmada') AS confirmadas,
                MAX(c.fecha) AS ultima_fecha
         FROM odontologo o
         LEFT JOIN cita c ON c.odontologo_id = o.id AND c.estado <> 'cancelada' {$andCita}
         GROUP BY o.id, o.nombre, o.especialidad, o.consultorio, o.activo
         ORDER BY total_citas DESC, finalizadas DESC, o.nombre ASC"
    );
    $stmtOdontologos->execute($paramsCita);
    $topOdontologos = $stmtOdontologos->fetchAll(PDO::FETCH_ASSOC);

    // 5. Horarios más solicitados
    $stmtHorarios = $conexion->prepare(
        "SELECT to_char(c.hora, 'HH24:MI') AS franja,
                COUNT(c.id) AS total
         FROM cita c
         {$whereCita}
         GROUP BY franja
         ORDER BY total DESC, franja ASC
         LIMIT 6"
    );
    $stmtHorarios->execute($paramsCita);
    $horariosPico = $stmtHorarios->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log('Error al generar reportes en recepción: ' . $e->getMessage());
    $error = 'No se pudieron cargar los reportes en este momento.';
}

$liderPaciente = ($topPacientes !== [] && (int) $topPacientes[0]['total_citas'] > 0) ? $topPacientes[0] : null;
$liderServicio = ($topServicios !== [] && (int) $topServicios[0]['total_solicitudes'] > 0) ? $topServicios[0] : null;
$liderOdontologo = ($topOdontologos !== [] && (int) $topOdontologos[0]['total_citas'] > 0) ? $topOdontologos[0] : null;

$maxCitasPaciente = $liderPaciente ? max(1, (int) $liderPaciente['total_citas']) : 1;
$totalCitasServicios = array_sum(array_map(static fn($r) => (int) $r['total_solicitudes'], $topServicios));
$maxCitasOdontologo = $liderOdontologo ? max(1, (int) $liderOdontologo['total_citas']) : 1;
$maxHorario = $horariosPico !== [] ? max(1, (int) $horariosPico[0]['total']) : 1;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportes Clínicos - DENTIX</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@500;600;700;800&family=Sora:wght@700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="../../assests/css/admin.css?v=20261103-2">
    <link rel="stylesheet" href="../../assests/css/index.css">
    <style>
        .report-kpi {
            background: #ffffff;
            border: 1px solid rgba(13, 148, 136, 0.14);
            border-radius: 18px;
            padding: 18px 20px;
            box-shadow: 0 8px 24px -12px rgba(15, 23, 42, 0.12);
            height: 100%;
        }
        .report-kpi__icon {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.15rem;
            flex-shrink: 0;
        }
        .spotlight-card {
            background: linear-gradient(135deg, #f0fdfa 0%, #ffffff 100%);
            border: 1px solid rgba(13, 148, 136, 0.22);
            border-radius: 18px;
            padding: 18px 20px;
            height: 100%;
        }
        .report-bar-track {
            width: 100%;
            height: 8px;
            background: #e2e8f0;
            border-radius: 999px;
            overflow: hidden;
        }
        .report-bar-fill {
            height: 100%;
            border-radius: 999px;
            background: linear-gradient(90deg, #0d9488 0%, #10b981 100%);
        }
        .rank-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 28px;
            height: 28px;
            border-radius: 999px;
            font-weight: 800;
            font-size: 0.8rem;
            background: #f1f5f9;
            color: #334155;
        }
        .rank-badge--1 {
            background: #ccfbf1;
            color: #0f766e;
            border: 1px solid #5eead4;
        }
        .panel__body {
            padding: 22px 24px;
        }
        .slot-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            padding: 14px 16px;
        }
        @media print {
            .sidebar, .topbar, .no-print, .app-footer {
                display: none !important;
            }
            .app-shell {
                display: block !important;
            }
            .app-main {
                margin-left: 0 !important;
                padding: 0 !important;
            }
            .panel {
                box-shadow: none !important;
                border: 1px solid #cbd5e1 !important;
                break-inside: avoid;
            }
        }
    </style>
</head>
<body class="admin-body" data-accent="citas">
    <div class="page-progress"><span class="page-progress__bar"></span></div>
    <div class="app-shell">

        <!-- BARRA LATERAL UNIFICADA -->
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
                <a href="../cita/lista_citas.php" class="side-link"><span class="side-link__icon"><i class="fa-solid fa-calendar-days"></i></span>Citas</a>
                <a href="reportes.php" class="side-link is-active"><span class="side-link__icon"><i class="fa-solid fa-chart-column"></i></span>Reportes</a>
            </nav>
            <?php require __DIR__ . '/boton_cerrar_sesion.php'; ?>
        </aside>

        <!-- CONTENIDO PRINCIPAL -->
        <main class="app-main">
            <header class="topbar">
                <button class="menu-btn" id="menu-toggle"><span></span><span></span><span></span></button>
                <div class="topbar__crumbs"><a href="../../index.php">Inicio</a><span>/</span><strong>Reportes</strong></div>
            </header>

            <div class="page">

                <?php if ($error !== ''): ?>
                    <div class="alert alert-danger py-2 small mb-3 border-0 shadow-sm">
                        <i class="fa-solid fa-triangle-exclamation me-2"></i> <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                <?php endif; ?>

                <!-- Cabecera y filtro de fechas -->
                <div class="panel mb-4">
                    <div class="panel__head flex-wrap gap-3">
                        <div>
                            <h3 class="panel__title"><i class="fa-solid fa-chart-column"></i> Reportes y Estadísticas de la Clínica</h3>
                            <p class="panel__subtitle">
                                Análisis en tiempo real de pacientes frecuentes, demanda de tratamientos y productividad del cuerpo médico.
                                <?php if ($fechaDesde !== '' || $fechaHasta !== ''): ?>
                                    <span class="badge bg-info bg-opacity-10 text-dark border ms-1">
                                        Período: <?php echo $fechaDesde !== '' ? htmlspecialchars($fechaDesde, ENT_QUOTES, 'UTF-8') : 'Inicio'; ?> → <?php echo $fechaHasta !== '' ? htmlspecialchars($fechaHasta, ENT_QUOTES, 'UTF-8') : 'Hoy'; ?>
                                    </span>
                                <?php endif; ?>
                            </p>
                        </div>
                        <div class="d-flex flex-wrap gap-2 align-items-center no-print">
                            <form action="reportes.php" method="GET" class="d-flex flex-wrap gap-2 align-items-center" style="margin: 0;">
                                <div class="input-group input-group-sm" style="width: auto;">
                                    <span class="input-group-text bg-light">Desde</span>
                                    <input type="date" name="desde" class="form-control" value="<?php echo htmlspecialchars($fechaDesde, ENT_QUOTES, 'UTF-8'); ?>">
                                </div>
                                <div class="input-group input-group-sm" style="width: auto;">
                                    <span class="input-group-text bg-light">Hasta</span>
                                    <input type="date" name="hasta" class="form-control" value="<?php echo htmlspecialchars($fechaHasta, ENT_QUOTES, 'UTF-8'); ?>">
                                </div>
                                <button type="submit" class="btn btn--primary btn--sm">
                                    <i class="fa-solid fa-filter"></i> Filtrar
                                </button>
                                <?php if ($fechaDesde !== '' || $fechaHasta !== ''): ?>
                                    <a href="reportes.php" class="btn btn-light btn-sm border" title="Ver todo el historial">
                                        <i class="fa-solid fa-rotate-left"></i> Limpiar
                                    </a>
                                <?php endif; ?>
                            </form>
                            <button type="button" onclick="window.print()" class="btn btn-outline-secondary btn-sm rounded-3 fw-semibold">
                                <i class="fa-solid fa-print me-1"></i> Imprimir
                            </button>
                        </div>
                    </div>

                    <!-- KPIs globales -->
                    <div class="panel__body">
                    <div class="row g-3">
                        <div class="col-sm-6 col-xl-3">
                            <div class="report-kpi d-flex align-items-center gap-3">
                                <div class="report-kpi__icon bg-primary bg-opacity-10 text-primary">
                                    <i class="fa-solid fa-calendar-check"></i>
                                </div>
                                <div>
                                    <div class="text-muted small fw-semibold">Total de Citas</div>
                                    <div class="fs-4 fw-bold text-dark"><?php echo (int) $resumen['total_citas']; ?></div>
                                    <div class="small text-muted">
                                        <?php echo (int) $resumen['pendientes']; ?> pend. · <?php echo (int) $resumen['confirmadas']; ?> conf.
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-sm-6 col-xl-3">
                            <div class="report-kpi d-flex align-items-center gap-3">
                                <div class="report-kpi__icon bg-success bg-opacity-10 text-success">
                                    <i class="fa-solid fa-check-double"></i>
                                </div>
                                <div>
                                    <div class="text-muted small fw-semibold">Citas Finalizadas</div>
                                    <div class="fs-4 fw-bold text-dark"><?php echo (int) $resumen['completadas']; ?></div>
                                    <div class="small text-muted">
                                        <?php echo (int) $resumen['canceladas']; ?> canceladas / no-show
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-sm-6 col-xl-3">
                            <div class="report-kpi d-flex align-items-center gap-3">
                                <div class="report-kpi__icon" style="background: #ccfbf1; color: #0d9488;">
                                    <i class="fa-solid fa-sack-dollar"></i>
                                </div>
                                <div>
                                    <div class="text-muted small fw-semibold">Ingresos Estimados</div>
                                    <div class="fs-4 fw-bold text-dark" data-cop-price="<?php echo htmlspecialchars((string) (float) $resumen['ingresos_estimados'], ENT_QUOTES, 'UTF-8'); ?>" data-price-format="table">$ <?php echo number_format((float) $resumen['ingresos_estimados'], 0, ',', '.'); ?></div>
                                    <div class="small text-muted">Citas confirmadas y completadas</div>
                                </div>
                            </div>
                        </div>

                        <div class="col-sm-6 col-xl-3">
                            <div class="report-kpi d-flex align-items-center gap-3">
                                <div class="report-kpi__icon bg-warning bg-opacity-10 text-warning">
                                    <i class="fa-solid fa-users"></i>
                                </div>
                                <div>
                                    <div class="text-muted small fw-semibold">Base Clínica</div>
                                    <div class="fs-4 fw-bold text-dark"><?php echo $totalPacientes; ?> <span class="fs-6 fw-normal text-muted">pacientes</span></div>
                                    <div class="small text-muted"><?php echo $totalOdontologosActivos; ?> odontólogos activos</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tarjetas Destacadas (Líderes) -->
                    <div class="row g-3 mt-2">
                        <div class="col-md-4">
                            <div class="spotlight-card">
                                <div class="small fw-bold text-uppercase mb-1" style="color: #0d9488; letter-spacing: 0.04em;">
                                    <i class="fa-solid fa-award me-1"></i> Paciente con más citas
                                </div>
                                <?php if ($liderPaciente): ?>
                                    <div class="fs-5 fw-bold text-dark"><?php echo htmlspecialchars($liderPaciente['nombre'], ENT_QUOTES, 'UTF-8'); ?></div>
                                    <div class="small text-muted">
                                        CC <?php echo htmlspecialchars($liderPaciente['cedula'] ?? 'N/D', ENT_QUOTES, 'UTF-8'); ?> ·
                                        <strong><?php echo (int) $liderPaciente['total_citas']; ?></strong> citas agendadas
                                    </div>
                                <?php else: ?>
                                    <div class="text-muted small">Sin citas registradas en este período.</div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="spotlight-card">
                                <div class="small fw-bold text-uppercase mb-1" style="color: #0d9488; letter-spacing: 0.04em;">
                                    <i class="fa-solid fa-star me-1"></i> Servicio más solicitado
                                </div>
                                <?php if ($liderServicio): ?>
                                    <div class="fs-5 fw-bold text-dark"><?php echo htmlspecialchars($liderServicio['nombre'], ENT_QUOTES, 'UTF-8'); ?></div>
                                    <div class="small text-muted">
                                        <strong><?php echo (int) $liderServicio['total_solicitudes']; ?></strong> solicitudes ·
                                        <span data-cop-price="<?php echo htmlspecialchars((string) (float) $liderServicio['ingresos_estimados'], ENT_QUOTES, 'UTF-8'); ?>" data-price-format="est">$ <?php echo number_format((float) $liderServicio['ingresos_estimados'], 0, ',', '.'); ?> est.</span>
                                    </div>
                                <?php else: ?>
                                    <div class="text-muted small">Sin solicitudes en este período.</div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="spotlight-card">
                                <div class="small fw-bold text-uppercase mb-1" style="color: #0d9488; letter-spacing: 0.04em;">
                                    <i class="fa-solid fa-user-doctor me-1"></i> Odontólogo con más citas
                                </div>
                                <?php if ($liderOdontologo): ?>
                                    <div class="fs-5 fw-bold text-dark"><?php echo htmlspecialchars($liderOdontologo['nombre'], ENT_QUOTES, 'UTF-8'); ?></div>
                                    <div class="small text-muted">
                                        <?php echo htmlspecialchars($liderOdontologo['especialidad'], ENT_QUOTES, 'UTF-8'); ?> ·
                                        <strong><?php echo (int) $liderOdontologo['total_citas']; ?></strong> citas (<?php echo (int) $liderOdontologo['finalizadas']; ?> finalizadas)
                                    </div>
                                <?php else: ?>
                                    <div class="text-muted small">Sin citas asignadas en este período.</div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    </div>
                </div>

                <!-- Reporte 1: Servicios más utilizados -->
                <div class="panel mb-4">
                    <div class="panel__head">
                        <div>
                            <h3 class="panel__title"><i class="fa-solid fa-notes-medical"></i> Servicios Más Utilizados y Pedidos</h3>
                            <p class="panel__subtitle">Demanda por tratamiento, participación porcentual e ingresos proyectados.</p>
                        </div>
                    </div>

                    <div class="table-wrap">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Servicio / Tratamiento</th>
                                    <th>Tarifa</th>
                                    <th>Total Pedidos</th>
                                    <th>Participación</th>
                                    <th class="text-center">Completadas / Conf. / Pend.</th>
                                    <th>Ingresos Est.</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($topServicios !== []): ?>
                                    <?php foreach ($topServicios as $i => $srv): ?>
                                        <?php
                                            $pedidos = (int) $srv['total_solicitudes'];
                                            $pct = $totalCitasServicios > 0 ? round(($pedidos / $totalCitasServicios) * 100, 1) : 0;
                                        ?>
                                        <tr>
                                            <td data-label="#">
                                                <span class="rank-badge <?php echo ($i === 0 && $pedidos > 0) ? 'rank-badge--1' : ''; ?>">
                                                    <?php echo $i + 1; ?>
                                                </span>
                                            </td>
                                            <td data-label="Servicio">
                                                <strong><?php echo htmlspecialchars($srv['nombre'], ENT_QUOTES, 'UTF-8'); ?></strong>
                                                <div class="text-muted small"><i class="fa-solid fa-clock me-1"></i> <?php echo (int) $srv['duracion_min']; ?> min</div>
                                            </td>
                                            <td data-label="Tarifa"><span class="money" data-cop-price="<?php echo htmlspecialchars((string) (float) $srv['precio'], ENT_QUOTES, 'UTF-8'); ?>" data-price-format="table">$ <?php echo number_format((float) $srv['precio'], 0, ',', '.'); ?></span></td>
                                            <td data-label="Pedidos"><strong class="fs-6"><?php echo $pedidos; ?></strong></td>
                                            <td data-label="Participación" style="min-width: 150px;">
                                                <div class="d-flex align-items-center gap-2">
                                                    <div class="report-bar-track flex-grow-1">
                                                        <div class="report-bar-fill" style="width: <?php echo $pct; ?>%;"></div>
                                                    </div>
                                                    <span class="small fw-bold text-secondary"><?php echo $pct; ?>%</span>
                                                </div>
                                            </td>
                                            <td data-label="Desglose" class="text-center">
                                                <span class="badge bg-success-subtle text-success me-1"><?php echo (int) $srv['completadas']; ?> comp.</span>
                                                <span class="badge bg-primary-subtle text-primary me-1"><?php echo (int) $srv['confirmadas']; ?> conf.</span>
                                                <span class="badge bg-warning-subtle text-warning"><?php echo (int) $srv['pendientes']; ?> pend.</span>
                                            </td>
                                            <td data-label="Ingresos Est.">
                                                <span class="money" data-cop-price="<?php echo htmlspecialchars((string) (float) $srv['ingresos_estimados'], ENT_QUOTES, 'UTF-8'); ?>" data-price-format="table">$ <?php echo number_format((float) $srv['ingresos_estimados'], 0, ',', '.'); ?></span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="7" class="text-center text-muted py-4">No hay servicios registrados.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Reporte 2: Odontólogos que más atienden -->
                <div class="panel mb-4">
                    <div class="panel__head">
                        <div>
                            <h3 class="panel__title"><i class="fa-solid fa-user-doctor"></i> Odontólogos con Mayor Atención y Citas</h3>
                            <p class="panel__subtitle">Productividad de especialistas ordenada por citas atendidas y asignadas (excluyendo canceladas).</p>
                        </div>
                    </div>

                    <div class="table-wrap">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Odontólogo</th>
                                    <th>Especialidad / Consultorio</th>
                                    <th>Total Citas</th>
                                    <th>Carga Relativa</th>
                                    <th class="text-center">Finalizadas / Confirmadas</th>
                                    <th>Última Cita</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($topOdontologos !== []): ?>
                                    <?php foreach ($topOdontologos as $i => $odo): ?>
                                        <?php
                                            $totOdo = (int) $odo['total_citas'];
                                            $pctOdo = $maxCitasOdontologo > 0 ? round(($totOdo / $maxCitasOdontologo) * 100, 1) : 0;
                                        ?>
                                        <tr>
                                            <td data-label="#">
                                                <span class="rank-badge <?php echo ($i === 0 && $totOdo > 0) ? 'rank-badge--1' : ''; ?>">
                                                    <?php echo $i + 1; ?>
                                                </span>
                                            </td>
                                            <td data-label="Odontólogo">
                                                <strong><?php echo htmlspecialchars($odo['nombre'], ENT_QUOTES, 'UTF-8'); ?></strong>
                                                <?php if (empty($odo['activo'])): ?>
                                                    <span class="badge bg-secondary-subtle text-secondary ms-1">Inactivo</span>
                                                <?php endif; ?>
                                            </td>
                                            <td data-label="Especialidad">
                                                <span class="chip chip--accent"><?php echo htmlspecialchars($odo['especialidad'], ENT_QUOTES, 'UTF-8'); ?></span>
                                                <div class="text-muted small mt-1"><?php echo htmlspecialchars($odo['consultorio'] ?? 'Sin consultorio', ENT_QUOTES, 'UTF-8'); ?></div>
                                            </td>
                                            <td data-label="Total Citas"><strong class="fs-6"><?php echo $totOdo; ?></strong></td>
                                            <td data-label="Carga" style="min-width: 140px;">
                                                <div class="report-bar-track">
                                                    <div class="report-bar-fill" style="width: <?php echo $pctOdo; ?>%;"></div>
                                                </div>
                                            </td>
                                            <td data-label="Desglose" class="text-center">
                                                <span class="badge bg-success-subtle text-success me-1"><?php echo (int) $odo['finalizadas']; ?> finalizadas</span>
                                                <span class="badge bg-primary-subtle text-primary"><?php echo (int) $odo['confirmadas']; ?> en agenda</span>
                                            </td>
                                            <td data-label="Última Cita" class="text-muted small">
                                                <?php echo !empty($odo['ultima_fecha']) ? htmlspecialchars($odo['ultima_fecha'], ENT_QUOTES, 'UTF-8') : 'Sin citas'; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="7" class="text-center text-muted py-4">No hay odontólogos registrados.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Reporte 3: Pacientes que más citas han agendado + Horarios pico -->
                <div class="row g-4">
                    <div class="col-lg-8">
                        <div class="panel h-100">
                            <div class="panel__head">
                                <div>
                                    <h3 class="panel__title"><i class="fa-solid fa-hospital-user"></i> Pacientes que Más Citas Han Agendado</h3>
                                    <p class="panel__subtitle">Ranking de pacientes ordenado por historial de solicitudes y asistencia.</p>
                                </div>
                            </div>

                            <div class="table-wrap">
                                <table class="data-table">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Paciente / Cédula</th>
                                            <th>Total Citas</th>
                                            <th>Frecuencia</th>
                                            <th class="text-center">Estado de sus Citas</th>
                                            <th>Última Cita</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if ($topPacientes !== []): ?>
                                            <?php foreach ($topPacientes as $i => $pac): ?>
                                                <?php
                                                    $totPac = (int) $pac['total_citas'];
                                                    $pctPac = $maxCitasPaciente > 0 ? round(($totPac / $maxCitasPaciente) * 100, 1) : 0;
                                                ?>
                                                <tr>
                                                    <td data-label="#">
                                                        <span class="rank-badge <?php echo ($i === 0 && $totPac > 0) ? 'rank-badge--1' : ''; ?>">
                                                            <?php echo $i + 1; ?>
                                                        </span>
                                                    </td>
                                                    <td data-label="Paciente">
                                                        <strong><?php echo htmlspecialchars($pac['nombre'], ENT_QUOTES, 'UTF-8'); ?></strong>
                                                        <div class="text-muted small">
                                                            <i class="fa-solid fa-id-card me-1"></i> <?php echo htmlspecialchars($pac['cedula'] ?? 'Sin cédula', ENT_QUOTES, 'UTF-8'); ?>
                                                            · <i class="fa-solid fa-phone me-1"></i> <?php echo htmlspecialchars($pac['telefono'], ENT_QUOTES, 'UTF-8'); ?>
                                                        </div>
                                                    </td>
                                                    <td data-label="Total"><strong class="fs-6"><?php echo $totPac; ?></strong></td>
                                                    <td data-label="Frecuencia" style="min-width: 110px;">
                                                        <div class="report-bar-track">
                                                            <div class="report-bar-fill" style="width: <?php echo $pctPac; ?>%;"></div>
                                                        </div>
                                                    </td>
                                                    <td data-label="Estados" class="text-center">
                                                        <span class="badge bg-success-subtle text-success me-1"><?php echo (int) $pac['completadas']; ?> comp.</span>
                                                        <span class="badge bg-primary-subtle text-primary me-1"><?php echo (int) $pac['confirmadas']; ?> conf.</span>
                                                        <span class="badge bg-warning-subtle text-warning me-1"><?php echo (int) $pac['pendientes']; ?> pend.</span>
                                                        <?php if ((int) $pac['canceladas'] > 0): ?>
                                                            <span class="badge bg-danger-subtle text-danger"><?php echo (int) $pac['canceladas']; ?> canc.</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td data-label="Última Cita" class="text-muted small">
                                                        <?php echo htmlspecialchars($pac['ultima_cita'], ENT_QUOTES, 'UTF-8'); ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr><td colspan="6" class="text-center text-muted py-4">Aún no hay pacientes con citas agendadas en este rango.</td></tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="panel h-100">
                            <div class="panel__head">
                                <div>
                                    <h3 class="panel__title"><i class="fa-solid fa-clock"></i> Horarios Más Solicitados</h3>
                                    <p class="panel__subtitle">Franjas horarias de mayor demanda.</p>
                                </div>
                            </div>

                            <div class="panel__body">
                                <?php if ($horariosPico !== []): ?>
                                    <div class="d-flex flex-column gap-3">
                                        <?php foreach ($horariosPico as $idx => $h): ?>
                                            <?php
                                                $cantH = (int) $h['total'];
                                                $pctH = round(($cantH / $maxHorario) * 100, 1);
                                                $pctTotal = (int) $resumen['total_citas'] > 0 ? round(($cantH / (int) $resumen['total_citas']) * 100, 1) : 0;
                                                $hora12 = date('g:i A', strtotime($h['franja']));
                                            ?>
                                            <div class="slot-card">
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <div class="d-flex align-items-center gap-2">
                                                        <span class="rank-badge <?php echo $idx === 0 ? 'rank-badge--1' : ''; ?>"><?php echo $idx + 1; ?></span>
                                                        <div>
                                                            <div class="fw-bold text-dark" style="font-size: 0.92rem;">
                                                                <i class="fa-regular fa-clock text-teal me-1"></i> <?php echo htmlspecialchars($h['franja'], ENT_QUOTES, 'UTF-8'); ?>
                                                                <span class="text-muted fw-normal small ms-1">(<?php echo htmlspecialchars($hora12, ENT_QUOTES, 'UTF-8'); ?>)</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="text-end">
                                                        <span class="badge bg-success-subtle text-success fw-bold px-2 py-1">
                                                            <?php echo $cantH . ($cantH === 1 ? ' cita' : ' citas'); ?>
                                                        </span>
                                                        <div class="text-muted" style="font-size: 0.72rem; margin-top: 2px;"><?php echo $pctTotal; ?>% del total</div>
                                                    </div>
                                                </div>
                                                <div class="report-bar-track">
                                                    <div class="report-bar-fill" style="width: <?php echo $pctH; ?>%;"></div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <p class="text-muted small text-center py-4 m-0">Sin citas registradas para calcular franjas horarias.</p>
                                <?php endif; ?>
                            </div>
                        </div>
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

