<?php
// view/odontologo/lista_odontologos.php - Listado de Odontólogos con Consultorio, Correo y Estado
require_once __DIR__ . '/../../app/recepcionista_auth.php';
requerir_recepcionista('../recepcionista/lista_recepcionista.php');
require_once "../../config/database.php";

$mensaje = $_SESSION['mensaje'] ?? null;
$tipo_mensaje = $_SESSION['tipo_mensaje'] ?? 'exito';
unset($_SESSION['mensaje'], $_SESSION['tipo_mensaje']);

try {
    // Obtenemos los odontólogos ordenados por ID junto con la cantidad de citas confirmadas que tienen
    $sql = "SELECT o.*, 
                   COUNT(c.id) AS total_citas_activas
            FROM odontologo o
            LEFT JOIN cita c ON c.odontologo_id = o.id AND c.estado = 'confirmada'
            GROUP BY o.id
            ORDER BY o.id ASC";
    $odontologos = $conexion->query($sql)->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $odontologos = [];
}

$csrfToken = token_csrf_recepcionista();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Odontólogos - DENTIX</title>
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
        
        <!-- BARRA LATERAL IDÉNTICA -->
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
                            <a href="form_odontologo.php" class="btn btn--primary btn--sm"><i class="fa-solid fa-user-plus"></i> Nuevo Odontólogo</a>
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
                                                    $tiene_cita = !empty($o['total_citas_activas']) && (int)$o['total_citas_activas'] > 0;
                                                    
                                                    if (!$esta_activo):
                                                ?>
                                                    <span class="badge bg-secondary bg-opacity-10 text-secondary px-3 py-2 rounded-pill fw-bold" style="font-size: 0.75rem;">
                                                        <i class="fa-solid fa-ban me-1"></i> Inactivo
                                                    </span>
                                                <?php elseif ($tiene_cita): ?>
                                                    <span class="badge bg-danger bg-opacity-10 text-danger px-3 py-2 rounded-pill fw-bold" style="font-size: 0.75rem;">
                                                        <i class="fa-solid fa-circle-dot me-1"></i> Ocupado / En Cita
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill fw-bold" style="font-size: 0.75rem;">
                                                        <i class="fa-solid fa-circle-check me-1"></i> Activo / Disponible
                                                    </span>
                                                <?php endif; ?>
                                            </td>

                                            <td data-label="Acciones" class="text-center">
                                                <div class="d-inline-flex gap-2 align-items-center justify-content-center">
                                                    <a href="editar_odontologo.php?id=<?php echo (int) $o['id']; ?>" class="action action--edit">
                                                        <i class="fa-solid fa-pen-to-square"></i> Editar
                                                    </a>
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
                                    <tr><td colspan="7" class="text-center text-muted py-4">No hay odontólogos registrados.</td></tr>
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
