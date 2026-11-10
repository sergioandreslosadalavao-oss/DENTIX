<?php
// view/paciente/lista_pacientes.php - Listado de Pacientes con Cédula, Correo, Fecha de Nacimiento y Alergias
require_once __DIR__ . '/../../app/recepcionista_auth.php';
requerir_recepcionista('../recepcionista/lista_recepcionista.php');
require_once "../../config/database.php";

try {
    // Obtenemos los pacientes ordenados por ID de manera descendente
    $pacientes = $conexion->query(
        "SELECT id, nombre, cedula, telefono, email, fecha_nac, alergias
         FROM paciente
         ORDER BY id DESC"
    )->fetchAll();
} catch (PDOException $e) {
    $pacientes = [];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pacientes - SCO</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@500;600;700;800&family=Sora:wght@700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="../../assests/css/admin.css?v=20261103-2">
    <link rel="stylesheet" href="../../assests/css/index.css">
</head>
<body class="admin-body" data-accent="pacientes">
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
                <a href="lista_pacientes.php" class="side-link is-active"><span class="side-link__icon"><i class="fa-solid fa-hospital-user"></i></span>Pacientes</a>
                <a href="../odontologo/lista_odontologos.php" class="side-link"><span class="side-link__icon"><i class="fa-solid fa-user-doctor"></i></span>Odontólogos</a>
                <a href="../servicio/lista_servicios.php" class="side-link"><span class="side-link__icon"><i class="fa-solid fa-notes-medical"></i></span>Servicios</a>
                <a href="../cita/lista_citas.php" class="side-link"><span class="side-link__icon"><i class="fa-solid fa-calendar-days"></i></span>Citas</a>
            </nav>
            <?php require __DIR__ . '/../recepcionista/boton_cerrar_sesion.php'; ?>
        </aside>

        <!-- CONTENIDO PRINCIPAL -->
        <main class="app-main">
            <header class="topbar">
                <button class="menu-btn" id="menu-toggle"><span></span><span></span><span></span></button>
                <div class="topbar__crumbs"><a href="../../index.php">Inicio</a><span>/</span><strong>Pacientes</strong></div>
            </header>

            <div class="page">
                <div class="panel">
                    <div class="panel__head">
                        <div>
                            <h3 class="panel__title"><i class="fa-solid fa-hospital-user"></i> Listado de Pacientes</h3>
                            <p class="panel__subtitle">Administración completa de expedientes, historia clínica básica y datos de contacto.</p>
                        </div>
                        <div class="panel__tools">
                            <a href="form_paciente.php" class="btn btn--primary btn--sm"><i class="fa-solid fa-user-plus"></i> Nuevo Paciente</a>
                        </div>
                    </div>

                    <div class="table-wrap">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Nombre</th>
                                    <th>Documento</th>
                                    <th>Teléfono / Correo</th>
                                    <th>Nacimiento</th>
                                    <th>Alergias</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($pacientes) > 0): ?>
                                    <?php foreach ($pacientes as $i => $p): ?>
                                        <tr>
                                            <td data-label="#"><span class="row-id"><?php echo $i + 1; ?></span></td>
                                            
                                            <!-- NOMBRE -->
                                            <td data-label="Nombre"><strong><?php echo htmlspecialchars($p['nombre']); ?></strong></td>
                                            
                                            <!-- DOCUMENTO (CÉDULA) -->
                                            <td data-label="Documento">
                                                <span class="badge bg-light text-dark border px-2 py-1 fw-bold">
                                                    <i class="fa-solid fa-id-card me-1 text-teal" style="color: #0d9488;"></i> <?php echo !empty($p['cedula']) ? htmlspecialchars($p['cedula']) : 'Sin registro'; ?>
                                                </span>
                                            </td>
                                            
                                            <!-- TELÉFONO Y CORREO -->
                                            <td data-label="Contacto">
                                                <div><i class="fa-solid fa-phone me-1 text-muted small"></i> <?php echo htmlspecialchars($p['telefono']); ?></div>
                                                <div class="text-muted small"><i class="fa-solid fa-envelope me-1"></i> <?php echo !empty($p['email']) ? htmlspecialchars($p['email']) : 'Sin correo'; ?></div>
                                            </td>

                                            <!-- FECHA DE NACIMIENTO -->
                                            <td data-label="Nacimiento">
                                                <span class="text-secondary small">
                                                    <i class="fa-solid fa-cake-candles me-1"></i> <?php echo !empty($p['fecha_nac']) ? htmlspecialchars($p['fecha_nac']) : 'No especificada'; ?>
                                                </span>
                                            </td>

                                            <!-- ALERGIAS (Con etiqueta destacada si tiene alguna) -->
                                            <td data-label="Alergias">
                                                <?php 
                                                    $alergias = trim($p['alergias'] ?? '');
                                                    if (!empty($alergias) && strtolower($alergias) !== 'ninguna' && strtolower($alergias) !== '[null]'): 
                                                ?>
                                                    <span class="badge bg-danger bg-opacity-10 text-danger px-2 py-1" style="font-size: 0.75rem;">
                                                        <i class="fa-solid fa-triangle-exclamation me-1"></i> <?php echo htmlspecialchars($alergias); ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge bg-success bg-opacity-10 text-success px-2 py-1" style="font-size: 0.75rem;">
                                                        <i class="fa-solid fa-shield-halved me-1"></i> Ninguna
                                                    </span>
                                                <?php endif; ?>
                                            </td>

                                            <!-- ACCIONES -->
                                            <td data-label="Acciones" class="text-center">
                                                <a href="editar_paciente.php?id=<?php echo $p['id']; ?>" class="action action--edit"><i class="fa-solid fa-pen-to-square"></i> Editar</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="7" class="text-center text-muted py-4">No hay pacientes registrados en este momento.</td></tr>
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