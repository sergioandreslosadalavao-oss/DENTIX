<?php
// lista_servicios.php - Listado de Servicios
require_once "../../config/database.php";
try {
    $servicios = $conexion->query("SELECT * FROM servicio ORDER BY id DESC")->fetchAll();
} catch (PDOException $e) {
    $servicios = [];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Servicios - SCO</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@500;600;700;800&family=Sora:wght@700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="../../assests/css/admin.css">
    <link rel="stylesheet" href="../../assests/css/index.css">
</head>
<body class="admin-body" data-accent="servicios">
    <div class="page-progress"><span class="page-progress__bar"></span></div>
    <div class="app-shell">
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
                <a href="lista_servicios.php" class="side-link is-active"><span class="side-link__icon"><i class="fa-solid fa-notes-medical"></i></span>Servicios</a>
                <a href="../cita/lista_citas.php" class="side-link"><span class="side-link__icon"><i class="fa-solid fa-calendar-days"></i></span>Citas</a>
            </nav>
        </aside>
        <main class="app-main">
            <header class="topbar">
                <button class="menu-btn" id="menu-toggle"><span></span><span></span><span></span></button>
                <div class="topbar__crumbs"><a href="../../index.php">Inicio</a><span>/</span><strong>Servicios</strong></div>
            </header>
            <div class="page">
                <div class="panel">
                    <div class="panel__head">
                        <div>
                            <h3 class="panel__title"><i class="fa-solid fa-notes-medical"></i> Catálogo de Servicios</h3>
                            <p class="panel__subtitle">Tratamientos odontológicos y tarifas.</p>
                        </div>
                        <div class="panel__tools">
                            <a href="form_servicio.php" class="btn btn--primary btn--sm"><i class="fa-solid fa-plus"></i> Nuevo Servicio</a>
                        </div>
                    </div>
                    <div class="table-wrap">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Nombre del Servicio</th>
                                    <th>Descripción</th>
                                    <th>Precio</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($servicios) > 0): ?>
                                    <?php foreach ($servicios as $i => $s): ?>
                                        <tr>
                                            <td data-label="#"><span class="row-id"><?php echo $i + 1; ?></span></td>
                                            <td data-label="Nombre"><strong><?php echo htmlspecialchars($s['nombre']); ?></strong></td>
                                            <td data-label="Descripción" class="text-muted"><?php echo htmlspecialchars($s['descripcion']); ?></td>
                                            <td data-label="Precio"><span class="money">$ <?php echo number_format($s['precio'], 0, ',', '.'); ?></span></td>
                                            <td data-label="Acciones" class="text-center">
                                                <a href="editar_servicio.php?id=<?php echo $s['id']; ?>" class="action action--edit"><i class="fa-solid fa-pen-to-square"></i> Editar</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="5" class="text-center text-muted py-4">No hay servicios registrados.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <footer class="app-footer"><div>&copy; 2026 DENTIX</div><div>ADSO - SENA</div></footer>
        </main>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
    <script src="../../assests/js/admin-animations.js"></script>
</body>
</html>