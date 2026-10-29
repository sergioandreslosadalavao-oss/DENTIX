<?php
// view/cita/lista_citas.php - Módulo de Citas con la estructura exacta y nativa de Servicios
require_once "../../config/database.php";
session_start();

$mensaje = "";
$error = "";

// 1. Asignar odontólogo manualmente a una cita
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['actualizar_cita'])) {
    $cita_id = $_POST['cita_id'] ?? '';
    $odontologo_id = $_POST['odontologo_id'] ?? '';

    if (!empty($cita_id) && !empty($odontologo_id)) {
        try {
            $stmt_update = $conexion->prepare("UPDATE cita SET odontologo_id = ? WHERE id = ?");
            $stmt_update->execute([$odontologo_id, $cita_id]);
            $mensaje = "¡Odontólogo asignado con éxito a la cita!";
        } catch (PDOException $e) {
            $error = "Error al actualizar la cita: " . $e->getMessage();
        }
    } else {
        $error = "Debe seleccionar un odontólogo de la lista.";
    }
}

// 2. Obtener lista de citas
try {
    $query = "SELECT c.id, c.fecha, c.hora, p.nombre AS paciente_nombre, p.cedula, 
                     o.id AS odontologo_id, o.nombre AS odontologo_nombre 
              FROM cita c 
              JOIN paciente p ON c.paciente_id = p.id 
              LEFT JOIN odontologo o ON c.odontologo_id = o.id 
              ORDER BY c.fecha DESC, c.hora DESC";
    $stmt_citas = $conexion->prepare($query);
    $stmt_citas->execute();
    $citas = $stmt_citas->fetchAll();
} catch (PDOException $e) {
    $citas = [];
    $error = "Error al cargar las citas: " . $e->getMessage();
}

// 3. Obtener odontólogos activos para el menú desplegable
try {
    $stmt_odonto = $conexion->prepare("SELECT id, nombre, especialidad FROM odontologo WHERE estado = 'activo' OR activo = true");
    $stmt_odonto->execute();
    $odontologos_activos = $stmt_odonto->fetchAll();
} catch (PDOException $e) {
    $odontologos_activos = [];
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
                                <?php if (count($citas) > 0): ?>
                                    <?php foreach ($citas as $index => $cita): ?>
                                        <tr>
                                            <td data-label="#"><span class="row-id"><?php echo $index + 1; ?></span></td>
                                            <td data-label="Paciente">
                                                <strong><?php echo htmlspecialchars($cita['paciente_nombre']); ?></strong>
                                                <div class="text-muted small"><i class="fa-solid fa-id-card me-1"></i> Cédula: <?php echo htmlspecialchars($cita['cedula']); ?></div>
                                            </td>
                                            <td data-label="Fecha y Hora">
                                                <div><i class="fa-solid fa-calendar-days text-teal me-1"></i> <strong><?php echo $cita['fecha']; ?></strong></div>
                                                <div class="text-muted small"><i class="fa-solid fa-clock me-1"></i> <?php echo $cita['hora']; ?></div>
                                            </td>
                                            <td data-label="Odontólogo">
                                                <form action="lista_citas.php" method="POST" class="d-flex gap-2 align-items: center;" id="form-cita-<?php echo $cita['id']; ?>">
                                                    <input type="hidden" name="actualizar_cita" value="1">
                                                    <input type="hidden" name="cita_id" value="<?php echo $cita['id']; ?>">
                                                    
                                                    <select name="odontologo_id" class="form-select form-select-sm" required style="border-radius: 8px;">
                                                        <option value="">-- Seleccionar --</option>
                                                        <?php foreach ($odontologos_activos as $odonto): ?>
                                                            <option value="<?php echo $odonto['id']; ?>" <?php echo ($cita['odontologo_id'] == $odonto['id']) ? 'selected' : ''; ?>>
                                                                <?php echo htmlspecialchars($odonto['nombre']); ?> (<?php echo htmlspecialchars($odonto['especialidad']); ?>)
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                            </td>
                                            <td data-label="Acciones" class="text-center">
                                                    <button type="submit" class="btn btn--primary btn--sm" style="display: inline-flex; align-items: center; gap: 6px;">
                                                        <i class="fa-solid fa-check"></i> Asignar
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">No hay citas registradas en este momento.</td>
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