<?php
// view/odontologo/lista_odontologos.php - Listado de Odontólogos con Consultorio, Correo y Estado
require_once __DIR__ . '/../../app/recepcionista_auth.php';
requerir_recepcionista('../recepcionista/lista_recepcionista.php');
require_once "../../config/database.php";
require_once __DIR__ . '/../../app/odontologo_catalogos.php';

$estadoConsultorios = obtenerEstadoConsultorios($conexion);
$todosConsultorios = $estadoConsultorios['todos'];
$consultoriosOcupados = $estadoConsultorios['ocupados'];
$consultoriosLibres = $estadoConsultorios['libres'];
$totalConsultorios = (int) ($estadoConsultorios['total'] ?? count($todosConsultorios));
$siguienteConsultorio = (string) ($estadoConsultorios['siguiente_sugerido'] ?? ('Consultorio ' . ($totalConsultorios + 1)));

$mensaje = $_SESSION['mensaje'] ?? null;
$tipo_mensaje = $_SESSION['tipo_mensaje'] ?? 'exito';
unset($_SESSION['mensaje'], $_SESSION['tipo_mensaje']);

try {
    // Calcula disponibilidad en tiempo real filtrando por la fecha y hora actuales de Colombia (RN-08 / RF-015)
    $sql = "SELECT o.*,
                   COUNT(c.id) FILTER (
                       WHERE c.fecha = :fecha_hoy::date
                         AND :hora_actual::time >= c.hora
                         AND :hora_actual::time < (c.hora + (COALESCE(s.duracion_min, 30) || ' minutes')::interval)
                   ) AS en_cita_ahora,
                   COUNT(c.id) FILTER (
                       WHERE c.fecha = :fecha_hoy::date
                   ) AS citas_hoy,
                   COUNT(c.id) FILTER (
                       WHERE c.fecha > :fecha_hoy::date
                   ) AS citas_futuras
            FROM odontologo o
            LEFT JOIN cita c ON c.odontologo_id = o.id AND c.estado = 'confirmada'
            LEFT JOIN servicio s ON s.id = c.servicio_id
            GROUP BY o.id
            ORDER BY o.id ASC";
    $stmtOdonto = $conexion->prepare($sql);
    $stmtOdonto->execute([
        ':fecha_hoy' => date('Y-m-d'),
        ':hora_actual' => date('H:i:s'),
    ]);
    $odontologos = $stmtOdonto->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $odontologos = [];
}

$csrfToken = token_csrf_recepcionista();
?>
<!DOCTYPE html>
<html lang="es" translate="no" class="notranslate">

<head>
    <meta charset="UTF-8">
    <meta name="google" content="notranslate">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Odontólogos - DENTIX</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@500;600;700;800&family=Sora:wght@700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/admin.css?v=20261103-2">
    <link rel="stylesheet" href="../../assets/css/index.css">
</head>

<body class="admin-body" data-accent="odontologos">
    <div class="page-progress"><span class="page-progress__bar"></span></div>
    <div class="app-shell">

        <!-- BARRA LATERAL IDÉNTICA -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar__top">
                <a href="../../index.php" class="brand">
                    <div class="brand__mark"><i class="fa-solid fa-tooth"></i></div>
                    <div class="brand__text">DENTIX<small>Panel Admin</small></div>
                </a>
                <button class="sidebar__close" id="sidebar-close"><svg viewBox="0 0 24 24">
                        <path d="M18 6L6 18M6 6l12 12" />
                    </svg></button>
            </div>
            <nav class="sidebar__nav">
                <span class="sidebar__label">Navegación</span>
                <a href="../../index.php" class="side-link"><span class="side-link__icon"><i class="fa-solid fa-house"></i></span>Inicio</a>
                <a href="../paciente/lista_pacientes.php" class="side-link"><span class="side-link__icon"><i class="fa-solid fa-hospital-user"></i></span>Pacientes</a>
                <a href="lista_odontologos.php" class="side-link is-active"><span class="side-link__icon"><i class="fa-solid fa-user-doctor"></i></span>Odontólogos</a>
                <a href="../servicio/lista_servicios.php" class="side-link"><span class="side-link__icon"><i class="fa-solid fa-notes-medical"></i></span>Servicios</a>
                <a href="../cita/lista_citas.php" class="side-link"><span class="side-link__icon"><i class="fa-solid fa-calendar-days"></i></span>Citas</a>
                <a href="../recepcionista/reportes.php" class="side-link"><span class="side-link__icon"><i class="fa-solid fa-chart-column"></i></span>Reportes</a>
            </nav>
            <?php require __DIR__ . '/../recepcionista/boton_cerrar_sesion.php'; ?>
        </aside>

        <!-- CONTENIDO PRINCIPAL -->
        <main class="app-main">
            <header class="topbar">
                <button class="menu-btn" id="menu-toggle"><span></span><span></span><span></span></button>
                <div class="topbar__crumbs"><a href="../../index.php">Inicio</a><span>/</span><strong>Odontólogos</strong></div>
            </header>

            <div class="page">
                <?php if ($mensaje !== null): ?>
                    <div class="alert <?php echo $tipo_mensaje === 'error' ? 'alert-danger' : 'alert-success'; ?> py-2 small mb-3 border-0 shadow-sm d-flex align-items-center">
                        <i class="fa-solid <?php echo $tipo_mensaje === 'error' ? 'fa-triangle-exclamation' : 'fa-circle-check'; ?> me-2"></i>
                        <div><?php echo htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8'); ?></div>
                    </div>
                <?php endif; ?>

                <div class="panel">
                    <div class="panel__head">
                        <div>
                            <h3 class="panel__title"><i class="fa-solid fa-user-doctor"></i> Cuerpo Médico</h3>
                            <p class="panel__subtitle">Especialistas activos, consultorio y disponibilidad en tiempo real.</p>
                        </div>
                        <div class="panel__tools">
                            <a href="#panel-consultorios" class="badge rounded-pill px-3 py-2 fw-semibold text-decoration-none" style="background: #f0fdfa; color: #0f766e; border: 1px solid #ccfbf1; font-size: 0.76rem;">
                                <i class="fa-solid fa-door-open me-1"></i> Gestión y Edición desde Consultorios
                            </a>
                        </div>
                    </div>

                    <div class="table-wrap">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Nombre</th>
                                    <th>Especialidad</th>
                                    <th>Consultorio</th>
                                    <th>Correo</th>
                                    <th class="text-center">Estado (Activo / En Cita)</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($odontologos) > 0): ?>
                                    <?php foreach ($odontologos as $i => $o): ?>
                                        <tr>
                                            <td data-label="#"><span class="row-id"><?php echo $i + 1; ?></span></td>
                                            <td data-label="Nombre"><strong><?php echo htmlspecialchars($o['nombre']); ?></strong></td>
                                            <td data-label="Especialidad"><span class="chip chip--accent"><?php echo htmlspecialchars($o['especialidad']); ?></span></td>

                                            <!-- CONSULTORIO -->
                                            <td data-label="Consultorio">
                                                <?php echo !empty($o['consultorio']) ? htmlspecialchars($o['consultorio']) : '<span class="text-muted small">Sin asignar</span>'; ?>
                                            </td>

                                            <!-- CORREO (Apunta a la columna 'email' de tu BD) -->
                                            <td data-label="Correo" class="text-muted">
                                                <?php echo !empty($o['email']) ? htmlspecialchars($o['email']) : '<span class="small">No registrado</span>'; ?>
                                            </td>

                                            <!-- ESTADO / DISPONIBILIDAD EN TIEMPO REAL -->
                                            <td data-label="Estado" class="text-center">
                                                <?php
                                                $esta_activo = !empty($o['activo']);
                                                $en_cita_ahora = !empty($o['en_cita_ahora']) && (int)$o['en_cita_ahora'] > 0;
                                                $tiene_cita_hoy = !empty($o['citas_hoy']) && (int)$o['citas_hoy'] > 0;
                                                $citas_futuras = (int)($o['citas_futuras'] ?? 0);

                                                if (!$esta_activo):
                                                ?>
                                                    <span class="badge bg-secondary bg-opacity-10 text-secondary px-3 py-2 rounded-pill fw-bold" style="font-size: 0.75rem;">
                                                        <i class="fa-solid fa-ban me-1"></i> Inactivo
                                                    </span>
                                                <?php elseif ($en_cita_ahora): ?>
                                                    <span class="badge bg-danger bg-opacity-10 text-danger px-3 py-2 rounded-pill fw-bold" style="font-size: 0.75rem;">
                                                        <i class="fa-solid fa-circle-dot me-1"></i> Ocupado / En Cita
                                                    </span>
                                                <?php elseif ($tiene_cita_hoy): ?>
                                                    <span class="badge bg-warning bg-opacity-10 text-warning-emphasis px-3 py-2 rounded-pill fw-bold" style="font-size: 0.75rem;">
                                                        <i class="fa-solid fa-calendar-day me-1"></i> Cita asignada hoy
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill fw-bold" style="font-size: 0.75rem;">
                                                        <i class="fa-solid fa-circle-check me-1"></i> Activo / Disponible
                                                    </span>
                                                    <?php if ($citas_futuras > 0): ?>
                                                        <div class="text-muted mt-1" style="font-size: 0.68rem;">
                                                            <i class="fa-regular fa-calendar me-1"></i><?php echo $citas_futuras; ?> programada(s) en días futuros
                                                        </div>
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                            </td>

                                            <td data-label="Acciones" class="text-center">
                                                <div class="d-inline-flex gap-2 align-items-center justify-content-center">
                                                    <form action="../../app/logica_odontologo.php" method="POST" style="margin: 0;" onsubmit="return confirm('¿Deseas eliminar a este odontólogo? Si tiene citas registradas no se eliminará.');">
                                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
                                                        <input type="hidden" name="accion" value="eliminar">
                                                        <input type="hidden" name="id" value="<?php echo (int) $o['id']; ?>">
                                                        <button type="submit" class="action action--delete border-0">
                                                            <i class="fa-solid fa-trash-can"></i> Eliminar
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-4">No hay odontólogos registrados.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- INTERFAZ DE CONSULTORIOS DINÁMICOS POR SEDE: CREAR, ELIMINAR LIBRES Y GESTIONAR ASIGNACIONES (RF-039 / RF-041 / RN-19 / RN-20) -->
                <div class="panel mt-4" id="panel-consultorios">
                    <div class="panel__head flex-wrap gap-2">
                        <div>
                            <h3 class="panel__title"><i class="fa-solid fa-door-open"></i> Consultorios de la Clínica</h3>
                            <p class="panel__subtitle">Control en tiempo real de los <?php echo $totalConsultorios; ?> consultorios configurados en la sede: crea o elimina espacios según la capacidad de tu clínica.</p>
                        </div>
                        <div class="panel__tools d-flex flex-wrap gap-2 align-items-center">
                            <span class="badge rounded-pill px-3 py-2 fw-bold" style="background: #fef3c7; color: #92400e; font-size: 0.76rem;">
                                <i class="fa-solid fa-door-closed me-1"></i> <?php echo count($consultoriosOcupados); ?> Ocupados (Activos)
                            </span>
                            <span class="badge rounded-pill px-3 py-2 fw-bold" style="background: #dcfce7; color: #166534; font-size: 0.76rem;">
                                <i class="fa-solid fa-door-open me-1"></i> <?php echo count($consultoriosLibres); ?> Libres de <?php echo $totalConsultorios; ?>
                            </span>
                            <button type="button" class="btn btn--primary btn--sm" data-bs-toggle="modal" data-bs-target="#modalNuevoConsultorio">
                                <i class="fa-solid fa-plus"></i> Nuevo Consultorio
                            </button>
                        </div>
                    </div>

                    <div class="p-4 pt-3">
                        <?php if ($totalConsultorios > 0): ?>
                        <div class="row g-3">
                            <?php foreach ($todosConsultorios as $cons): ?>
                                <div class="col-12 col-sm-6 col-lg-4 col-xl-3">
                                    <?php if ($cons['ocupado']): ?>
                                        <div class="h-100 p-3 rounded-4 border d-flex flex-column justify-content-between" style="background: linear-gradient(160deg, #ffffff 0%, #fffbeb 100%); border-color: rgba(217, 119, 6, 0.28) !important;">
                                            <div>
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <span class="fw-bold text-dark" style="font-family: 'Sora', sans-serif; font-size: 0.95rem;">
                                                        <i class="fa-solid fa-door-closed me-1" style="color: #d97706;"></i><?php echo htmlspecialchars($cons['nombre'], ENT_QUOTES, 'UTF-8'); ?>
                                                    </span>
                                                    <span class="badge rounded-pill" style="background: #fef3c7; color: #92400e; font-size: 0.68rem;">Ocupado</span>
                                                </div>
                                                <div class="fw-bold text-dark small mb-1">
                                                    <i class="fa-solid fa-user-doctor me-1 text-teal" style="color: #0d9488;"></i>
                                                    <?php echo htmlspecialchars($cons['odontologo']['nombre'], ENT_QUOTES, 'UTF-8'); ?>
                                                </div>
                                                <div class="text-muted mb-3" style="font-size: 0.76rem;">
                                                    <?php echo htmlspecialchars($cons['odontologo']['especialidad'], ENT_QUOTES, 'UTF-8'); ?>
                                                </div>
                                            </div>
                                            <a href="editar_odontologo.php?id=<?php echo (int) $cons['odontologo']['id']; ?>" class="btn btn-sm rounded-pill fw-bold w-100" style="background: #fef3c7; color: #92400e; border: 1px solid rgba(217, 119, 6, 0.35); font-size: 0.75rem;">
                                                <i class="fa-solid fa-pen-to-square me-1"></i>Gestionar asignación
                                            </a>
                                        </div>
                                    <?php else: ?>
                                        <div class="h-100 p-3 rounded-4 border d-flex flex-column justify-content-between" style="background: linear-gradient(160deg, #ffffff 0%, #f0fdfa 100%); border-color: rgba(13, 148, 136, 0.28) !important;">
                                            <div>
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <span class="fw-bold text-dark" style="font-family: 'Sora', sans-serif; font-size: 0.95rem;">
                                                        <i class="fa-solid fa-door-open me-1" style="color: #0d9488;"></i><?php echo htmlspecialchars($cons['nombre'], ENT_QUOTES, 'UTF-8'); ?>
                                                    </span>
                                                    <span class="badge rounded-pill" style="background: #dcfce7; color: #166534; font-size: 0.68rem;">Libre</span>
                                                </div>
                                                <p class="text-muted mb-3" style="font-size: 0.76rem;">Espacio disponible para asignar a un nuevo odontólogo.</p>
                                            </div>
                                            <div class="d-flex align-items-center gap-2">
                                                <a href="form_odontologo.php?consultorio=<?php echo urlencode($cons['nombre']); ?>" class="btn btn-sm rounded-pill fw-bold flex-grow-1" style="background: #e6f9f3; color: #0a7a70; border: 1px solid rgba(13, 148, 136, 0.3); font-size: 0.75rem;">
                                                    <i class="fa-solid fa-plus me-1"></i>Asignar aquí
                                                </a>
                                                <?php if (!empty($cons['id'])): ?>
                                                    <form action="../../app/logica_consultorio.php" method="POST" style="margin: 0;" onsubmit="return confirm('¿Deseas eliminar el consultorio &quot;<?php echo htmlspecialchars($cons['nombre'], ENT_QUOTES, 'UTF-8'); ?>&quot; de esta sede?');">
                                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
                                                        <input type="hidden" name="accion" value="eliminar">
                                                        <input type="hidden" name="id" value="<?php echo (int) $cons['id']; ?>">
                                                        <button type="submit" class="btn btn-sm rounded-pill px-2 fw-bold" title="Eliminar consultorio" aria-label="Eliminar consultorio" style="background: #fef2f2; color: #dc2626; border: 1px solid rgba(220, 38, 38, 0.28); font-size: 0.75rem;">
                                                            <i class="fa-solid fa-trash-can"></i>
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <?php else: ?>
                            <div class="text-center py-4 px-3 rounded-4 border" style="background: #f8fafc;">
                                <i class="fa-solid fa-door-open fs-2 text-muted mb-2"></i>
                                <p class="text-muted small mb-3">Aún no hay consultorios configurados en esta sede.</p>
                                <button type="button" class="btn btn--primary btn--sm" data-bs-toggle="modal" data-bs-target="#modalNuevoConsultorio">
                                    <i class="fa-solid fa-plus"></i> Crear primer consultorio
                                </button>
                            </div>
                        <?php endif; ?>

                        <div class="row g-3 mt-3 pt-3 border-top">
                            <div class="col-md-6">
                                <div class="small fw-bold text-dark mb-2">
                                    <i class="fa-solid fa-lock me-1" style="color: #d97706;"></i> Consultorios activos / ocupados (<?php echo count($consultoriosOcupados); ?>):
                                </div>
                                <div class="d-flex flex-wrap gap-2">
                                    <?php if (count($consultoriosOcupados) > 0): ?>
                                        <?php foreach ($consultoriosOcupados as $cOcu): ?>
                                            <a href="editar_odontologo.php?id=<?php echo (int) $cOcu['odontologo']['id']; ?>" class="badge rounded-pill text-decoration-none border px-3 py-2 fw-semibold" style="background: #fffbeb; color: #78350f; border-color: #fde68a !important; font-size: 0.74rem;">
                                                <i class="fa-solid fa-pen-to-square me-1"></i><?php echo htmlspecialchars($cOcu['nombre'] . ' · ' . $cOcu['odontologo']['nombre'], ENT_QUOTES, 'UTF-8'); ?>
                                            </a>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <span class="text-muted small">Ningún consultorio ocupado actualmente.</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="small fw-bold text-dark mb-2">
                                    <i class="fa-solid fa-circle-check me-1" style="color: #10b981;"></i> Listado de consultorios libres disponibles (<?php echo count($consultoriosLibres); ?>):
                                </div>
                                <div class="d-flex flex-wrap gap-2">
                                    <?php if (count($consultoriosLibres) > 0): ?>
                                        <?php foreach ($consultoriosLibres as $cLib): ?>
                                            <a href="form_odontologo.php?consultorio=<?php echo urlencode($cLib['nombre']); ?>" class="badge rounded-pill text-decoration-none border px-3 py-2 fw-semibold" style="background: #ecfdf5; color: #065f46; border-color: #a7f3d0 !important; font-size: 0.74rem;">
                                                <i class="fa-solid fa-plus me-1"></i><?php echo htmlspecialchars($cLib['nombre'], ENT_QUOTES, 'UTF-8'); ?>
                                            </a>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <span class="text-muted small">No quedan consultorios libres (<?php echo count($consultoriosOcupados); ?> de <?php echo $totalConsultorios; ?> ocupados). Crea uno nuevo si tu sede cuenta con más espacios.</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <?php
                        $odontologosInactivos = array_values(array_filter($odontologos, static fn(array $o): bool => empty($o['activo'])));
                        if (count($odontologosInactivos) > 0):
                        ?>
                            <div class="mt-3 pt-3 border-top">
                                <div class="small fw-bold text-dark mb-2">
                                    <i class="fa-solid fa-user-slash me-1 text-secondary"></i> Odontólogos inactivos (sin consultorio activo — <?php echo count($odontologosInactivos); ?>):
                                </div>
                                <div class="d-flex flex-wrap gap-2">
                                    <?php foreach ($odontologosInactivos as $oIna): ?>
                                        <a href="editar_odontologo.php?id=<?php echo (int) $oIna['id']; ?>" class="badge rounded-pill text-decoration-none border px-3 py-2 fw-semibold" style="background: #f1f5f9; color: #334155; border-color: #cbd5e1 !important; font-size: 0.74rem;">
                                            <i class="fa-solid fa-pen-to-square me-1"></i><?php echo htmlspecialchars($oIna['nombre'] . ' · Gestionar asignación', ENT_QUOTES, 'UTF-8'); ?>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <footer class="app-footer">
                <div>&copy; 2026 DENTIX</div>
                <div>ADSO - SENA</div>
            </footer>
        </main>
    </div>

    <!-- MODAL CREAR NUEVO CONSULTORIO (RF-041 / RN-20) -->
    <div class="modal fade" id="modalNuevoConsultorio" tabindex="-1" aria-labelledby="modalNuevoConsultorioLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                <form action="../../app/logica_consultorio.php" method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
                    <input type="hidden" name="accion" value="crear">

                    <div class="modal-header border-0 px-4 pt-4 pb-2" style="background: linear-gradient(135deg, #f0fdfa 0%, #ffffff 100%);">
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-3 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; background: #ccfbf1; color: #0d9488; font-size: 1.2rem;">
                                <i class="fa-solid fa-door-open"></i>
                            </div>
                            <div>
                                <h5 class="modal-title fw-bold text-dark mb-0" id="modalNuevoConsultorioLabel" style="font-family: 'Sora', sans-serif;">Nuevo Consultorio</h5>
                                <small class="text-muted">Adapta la cantidad de consultorios a la capacidad real de tu sede.</small>
                            </div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>

                    <div class="modal-body px-4 py-3">
                        <div class="mb-2">
                            <label for="nuevoConsultorioNombre" class="form-label small fw-semibold text-dark">Nombre o Número del Consultorio</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-door-closed text-muted"></i></span>
                                <input
                                    type="text"
                                    class="form-control border-start-0"
                                    id="nuevoConsultorioNombre"
                                    name="nombre"
                                    minlength="2"
                                    maxlength="30"
                                    value="<?php echo htmlspecialchars($siguienteConsultorio, ENT_QUOTES, 'UTF-8'); ?>"
                                    placeholder="Ej. Consultorio 11 o Box Cirugía"
                                    required
                                >
                            </div>
                            <small class="text-muted d-block mt-1">Se sugiere automáticamente el siguiente consecutivo disponible, pero puedes escribir el nombre propio de tu sede (2 a 30 caracteres).</small>
                        </div>
                    </div>

                    <div class="modal-footer border-0 px-4 pb-4 pt-2 gap-2">
                        <button type="button" class="btn btn-light rounded-pill px-4 fw-semibold" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn text-white rounded-pill px-4 fw-bold shadow-sm" style="background-color: #0d9488; border: none;">
                            <i class="fa-solid fa-plus me-1"></i> Crear Consultorio
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
    <script src="../../assets/js/admin-animations.js"></script>
    <script src="../../assets/js/dentix-i18n.js?v=20261120-i18nfix"></script>
</body>

</html>