<?php
// form_cita.php - Registro de Citas con diseño administrativo unificado
require_once "../../config/database.php";
require_once "../../app/sesion.php";
session_start();

// Agenda administrativa: exige sesión de recepción activa
sesion_requiere_recepcionista("../recepcionista/login_recepcionista.php");

try {
    $pacientes = $conexion->query("SELECT id, nombre FROM paciente ORDER BY nombre")->fetchAll();
    $odontologos = $conexion->query("SELECT id, nombre FROM odontologo ORDER BY nombre")->fetchAll();
    $servicios = $conexion->query("SELECT id, nombre FROM servicio ORDER BY nombre")->fetchAll();
} catch (PDOException $e) {
    $pacientes = $odontologos = $servicios = [];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agendar Nueva Cita - SCO</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@500;600;700;800&family=Sora:wght@700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    
    <link rel="stylesheet" href="../../assests/css/admin.css">
    <link rel="stylesheet" href="../../assests/css/index.css">
</head>
<body class="admin-body">

    <div class="page-progress">
        <span class="page-progress__bar"></span>
    </div>

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
                <a href="../../index.php" class="side-link"><span class="side-link__icon"><i class="fa-solid fa-house"></i></span>Inicio / Landing</a>
                <a href="../paciente/lista_pacientes.php" class="side-link"><span class="side-link__icon"><i class="fa-solid fa-hospital-user"></i></span>Pacientes</a>
                <a href="../odontologo/lista_odontologos.php" class="side-link"><span class="side-link__icon"><i class="fa-solid fa-user-doctor"></i></span>Odontólogos</a>
                <a href="../servicio/lista_servicios.php" class="side-link"><span class="side-link__icon"><i class="fa-solid fa-notes-medical"></i></span>Servicios</a>
                <a href="lista_citas.php" class="side-link is-active"><span class="side-link__icon"><i class="fa-solid fa-calendar-days"></i></span>Agenda de Citas</a>
            </nav>
        </aside>

        <main class="app-main">
            <header class="topbar">
                <button class="menu-btn" id="menu-toggle" aria-label="Abrir menú"><span></span><span></span><span></span></button>
                <div class="topbar__crumbs">
                    <a href="lista_citas.php">Agenda de Citas</a>
                    <span>/</span>
                    <strong>Nueva Cita</strong>
                </div>
            </header>

            <div class="page">
                <div class="form-head">
                    <span class="eyebrow">Programación</span>
                    <h1>Agendar <em>Nueva Cita</em></h1>
                    <p>Selecciona el paciente, el odontólogo activo y el servicio requerido.</p>
                </div>

                <div class="form-card">
                    <form action="../../app/logica_cita.php" method="POST">
                        <div class="form-grid">
                            <div class="field field--full">
                                <label class="field__label">Paciente</label>
                                <select name="paciente_id" class="form-control" required>
                                    <option value="">Seleccione un paciente...</option>
                                    <?php foreach ($pacientes as $p): ?>
                                        <option value="<?php echo $p['id']; ?>"><?php echo htmlspecialchars($p['nombre']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="field">
                                <label class="field__label">Odontólogo Activo</label>
                                <select name="odontologo_id" class="form-control" required>
                                    <option value="">Seleccione especialista...</option>
                                    <?php foreach ($odontologos as $o): ?>
                                        <option value="<?php echo $o['id']; ?>"><?php echo htmlspecialchars($o['nombre']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="field">
                                <label class="field__label">Servicio Odontológico</label>
                                <select name="servicio_id" class="form-control" required>
                                    <option value="">Seleccione servicio...</option>
                                    <?php foreach ($servicios as $s): ?>
                                        <option value="<?php echo $s['id']; ?>"><?php echo htmlspecialchars($s['nombre']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="field">
                                <label class="field__label">Fecha</label>
                                <input type="date" name="fecha" class="form-control" required>
                            </div>

                            <div class="field">
                                <label class="field__label">Hora</label>
                                <input type="time" name="hora" class="form-control" required>
                            </div>

                            <div class="field field--full">
                                <label class="field__label">Motivo de consulta</label>
                                <textarea name="motivo" class="form-control" rows="3" placeholder="Descripción breve..." required></textarea>
                            </div>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn btn--primary">
                                <i class="fa-solid fa-floppy-disk"></i> Guardar Cita
                            </button>
                            <a href="lista_citas.php" class="btn btn--ghost">Cancelar</a>
                        </div>
                    </form>
                </div>
            </div>

            <footer class="app-footer">
                <div>&copy; 2026 DENTIX. Sistema Odontológico.</div>
                <div>ADSO - SENA</div>
            </footer>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
    <script src="../../assests/js/admin-animations.js"></script>
</body>
</html>