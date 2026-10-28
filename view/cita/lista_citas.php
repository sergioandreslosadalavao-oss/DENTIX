<?php
// lista_citas.php - Panel de recepcionista con el diseño exacto del módulo de servicios
require_once "../../config/database.php";
session_start();

$mensaje = "";
$error = "";

// 1. Manejar la asignación del odontólogo
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
        $error = "Debe seleccionar un odontólogo válido.";
    }
}

// 2. Obtener la lista de citas entrantes
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
    $error = "Error al cargar la lista de citas: " . $e->getMessage();
}

// 3. Obtener odontólogos activos
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
    <title>Citas Entrantes - DENTIX Admin</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@500;600;700;800&family=Sora:wght@700;800&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome para Iconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    
    <!-- HOJAS DE ESTILOS GLOBALES DE LA CARPETA ASSESTS -->
    <link rel="stylesheet" href="../../assests/css/admin.css">
    <link rel="stylesheet" href="../../assests/css/index.css">
</head>
<body class="admin-body">

    <!-- Layout Principal idéntico al de Servicios -->
    <div class="admin-layout" style="display: flex; min-height: 100vh; position: relative;">
        
        <!-- Barra Lateral Fija con el menú idéntico -->
        <aside class="sidebar" style="width: 260px; min-width: 260px; position: fixed; top: 0; left: 0; height: 100vh; background: #07221e; color: white; padding: 24px; display: flex; flex-direction: column; z-index: 1000; overflow-y: auto;">
            <div class="brand d-flex align-items-center gap-2 mb-4 pb-3 border-bottom border-light border-opacity-10">
                <div class="brand__mark" style="width: 40px; height: 40px; display: grid; place-items: center; background: linear-gradient(140deg, #3fd9c5, #10a294); border-radius: 12px; color: white;">
                    <i class="fa-solid fa-tooth"></i>
                </div>
                <div class="brand__text fw-bold" style="font-size: 1.1rem; line-height: 1.2;">
                    DENTIX
                    <small class="d-block text-white-50 fw-normal" style="font-size: 0.65rem;">PANEL ADMIN</small>
                </div>
            </div>
            
            <span class="text-uppercase text-white-50 fw-bold mb-3 d-block" style="font-size: 0.65rem; letter-spacing: 1.2px;">Navegación</span>
            <ul class="nav flex-column gap-2" style="list-style: none; padding: 0;">
                <li><a href="../../index.php" class="nav-link text-white-50 py-2 px-3 rounded text-decoration-none d-block"><i class="fa-solid fa-house me-2"></i> Inicio</a></li>
                <li><a href="../paciente/lista_pacientes.php" class="nav-link text-white-50 py-2 px-3 rounded text-decoration-none d-block"><i class="fa-solid fa-hospital-user me-2"></i> Pacientes</a></li>
                <li><a href="../odontologo/lista_odontologos.php" class="nav-link text-white-50 py-2 px-3 rounded text-decoration-none d-block"><i class="fa-solid fa-user-doctor me-2"></i> Odontólogos</a></li>
                <li><a href="../servicio/lista_servicios.php" class="nav-link text-white-50 py-2 px-3 rounded text-decoration-none d-block"><i class="fa-solid fa-notes-medical me-2"></i> Servicios</a></li>
                <!-- Elemento Activo con el estilo exacto azulado/morado de la captura -->
                <li><a href="lista_citas.php" class="nav-link py-2 px-3 rounded text-decoration-none d-block text-white fw-bold" style="background: linear-gradient(135deg, #4f46e5, #3b82f6); box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);"><i class="fa-solid fa-calendar-days me-2"></i> Citas</a></li>
            </ul>
        </aside>

        <!-- Contenido Central con el margen izquierdo exacto -->
        <main class="main-content flex-grow-1 p-4 p-md-5" style="margin-left: 260px; width: calc(100% - 260px); background-color: #f4f7fa; min-height: 100vh; display: flex; flex-direction: column; justify-content: space-between;">
            <div>
                <!-- Migas de pan idénticas -->
                <div class="mb-4">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0 small" style="background: transparent; padding: 0;">
                            <li class="breadcrumb-item"><a href="../../index.php" class="text-decoration-none text-primary fw-semibold">Inicio</a></li>
                            <li class="breadcrumb-item active text-muted" aria-current="page">Citas Entrantes</li>
                        </ol>
                    </nav>
                </div>

                <!-- Alertas -->
                <?php if (!empty($mensaje)): ?>
                    <div class="alert alert-success py-2 small mb-4 shadow-sm border-0"><i class="fa-solid fa-circle-check me-2"></i><?php echo $mensaje; ?></div>
                <?php endif; ?>
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger py-2 small mb-4 shadow-sm border-0"><i class="fa-solid fa-triangle-exclamation me-2"></i><?php echo $error; ?></div>
                <?php endif; ?>

                <!-- Tarjeta Principal con la estructura limpia de Servicios -->
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-5">
                    <div class="card-header bg-white py-4 px-4 px-md-5 border-bottom d-flex justify-content-between align-items-center flex-wrap gap-3">
                        <div>
                            <h4 class="fw-bold text-dark mb-1 d-flex align-items-center">
                                <i class="fa-solid fa-calendar-check text-primary me-2"></i> Control de Citas Entrantes
                            </h4>
                            <p class="text-muted small mb-0">Gestión y asignación de odontólogos activos para las citas solicitadas.</p>
                        </div>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead class="table-light text-uppercase small text-muted">
                                <tr>
                                    <th class="py-3 px-4" style="width: 80px;">#</th>
                                    <th class="py-3">Paciente (Cédula)</th>
                                    <th class="py-3">Odontólogo Asignado</th>
                                    <th class="py-3">Fecha y Hora</th>
                                    <th class="py-3 text-center px-4">Acción / Asignar Especialista</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($citas) > 0): ?>
                                    <?php foreach ($citas as $index => $cita): ?>
                                        <tr>
                                            <td class="px-4">
                                                <span class="badge bg-light text-dark fw-bold px-2 py-1 rounded-2 border">
                                                    <?php echo $index + 1; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="fw-bold text-dark"><?php echo htmlspecialchars($cita['paciente_nombre']); ?></div>
                                                <small class="text-muted"><i class="fa-solid fa-id-card me-1"></i> Cédula: <?php echo htmlspecialchars($cita['cedula']); ?></small>
                                            </td>
                                            <td>
                                                <?php if (!empty($cita['odontologo_nombre'])): ?>
                                                    <span class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill fw-semibold">
                                                        <i class="fa-solid fa-user-doctor me-1"></i> <?php echo htmlspecialchars($cita['odontologo_nombre']); ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge bg-warning bg-opacity-10 text-warning px-3 py-2 rounded-pill fw-semibold">
                                                        <i class="fa-solid fa-triangle-exclamation me-1"></i> Sin asignar
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="small fw-semibold text-dark"><i class="fa-solid fa-calendar text-muted me-1"></i> <?php echo $cita['fecha']; ?></div>
                                                <div class="small text-muted"><i class="fa-solid fa-clock text-muted me-1"></i> <?php echo $cita['hora']; ?></div>
                                            </td>
                                            <td class="px-4 text-center">
                                                <!-- Formulario de asignación alineado -->
                                                <form action="lista_citas.php" method="POST" class="d-flex justify-content-center align-items-center gap-2">
                                                    <input type="hidden" name="actualizar_cita" value="1">
                                                    <input type="hidden" name="cita_id" value="<?php echo $cita['id']; ?>">
                                                    
                                                    <select name="odontologo_id" class="form-select form-select-sm" style="max-width: 170px;" required>
                                                        <option value="">-- Seleccionar --</option>
                                                        <?php foreach ($odontologos_activos as $odonto): ?>
                                                            <option value="<?php echo $odonto['id']; ?>" <?php echo ($cita['odontologo_id'] == $odonto['id']) ? 'selected' : ''; ?>>
                                                                <?php echo htmlspecialchars($odonto['nombre']); ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                    
                                                    <button type="submit" class="btn btn-sm px-3 fw-bold text-white rounded-pill" style="background: #0d9488; border: none;">
                                                        <i class="fa-solid fa-check me-1"></i> Asignar
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-5 text-muted">
                                            <i class="fa-regular fa-calendar-xmark fs-2 mb-2 d-block"></i>
                                            No hay citas registradas en este momento.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Footer idéntico -->
            <footer class="mt-5 pt-3 border-top text-muted small d-flex justify-content-between align-items-center">
                <span>&copy; 2026 DENTIX</span>
                <span>ADSO - SENA</span>
            </footer>
        </main>
    </div>

    <!-- Bootstrap JS y Librerías -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
    <script src="../../assests/js/admin-animations.js"></script>
</body>
</html>