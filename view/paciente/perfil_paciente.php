<?php
require_once __DIR__ . '/../../app/paciente_auth.php';
iniciar_sesion_paciente();

if (!paciente_autenticado()) {
    header('Location: login_paciente.php');
    exit;
}

header('Cache-Control: no-store, private');
header('Pragma: no-cache');

require_once __DIR__ . '/../../config/database.php';

try {
    $stmt = $conexion->prepare(
        'SELECT id, nombre, cedula, telefono, email, fecha_nac, alergias
         FROM paciente
         WHERE id = ?'
    );
    $stmt->execute([$_SESSION['paciente_id']]);
    $paciente = $stmt->fetch();

    if (!$paciente) {
        $_SESSION = [];
        session_destroy();
        header('Location: login_paciente.php');
        exit;
    }

    $stmt = $conexion->prepare(
        "SELECT c.id, c.fecha, c.hora, c.estado::text AS estado,
                c.motivo, c.motivo_cancelacion,
                s.nombre AS servicio_nombre,
                o.nombre AS odontologo_nombre,
                o.especialidad AS odontologo_especialidad
         FROM cita c
         JOIN servicio s ON s.id = c.servicio_id
         LEFT JOIN odontologo o ON o.id = c.odontologo_id
         WHERE c.paciente_id = ?
         ORDER BY c.fecha DESC, c.hora DESC, c.id DESC"
    );
    $stmt->execute([$paciente['id']]);
    $citas = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log('Error al cargar perfil del paciente: ' . $e->getMessage());
    http_response_code(500);
    exit('No se pudo cargar tu perfil. Inténtalo nuevamente más tarde.');
}

$totalCitas = count($citas);
$pendientes = count(array_filter($citas, static fn(array $cita): bool => $cita['estado'] === 'pendiente'));
$confirmadas = count(array_filter($citas, static fn(array $cita): bool => $cita['estado'] === 'confirmada'));
$flash = $_SESSION['paciente_flash'] ?? '';
unset($_SESSION['paciente_flash']);
$csrfToken = token_csrf_paciente();

$estadoLabels = [
    'pendiente' => 'Pendiente',
    'confirmada' => 'Confirmada',
    'completada' => 'Completada',
    'cancelada' => 'Cancelada',
    'no_show' => 'No asistió',
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi perfil - DENTIX</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@500;600;700;800&family=Sora:wght@700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="../../assests/css/admin.css">
    <link rel="stylesheet" href="../../assests/css/paciente.css?v=20261110-vivid">
</head>
<body class="admin-body patient-profile-body">
    <div class="page-progress"><span class="page-progress__bar"></span></div>
    <div class="patient-profile-shell">
        <header class="patient-profile-topbar">
            <a href="../../index.php" class="brand">
                <span class="brand__mark"><i class="fa-solid fa-tooth"></i></span>
                <span class="brand__text">DENTIX<small>Portal del paciente</small></span>
            </a>
            <nav class="patient-profile-nav" aria-label="Navegación del paciente">
                <a class="is-current" href="perfil_paciente.php"><i class="fa-solid fa-user"></i> Mi perfil</a>
                <a href="portal_paciente.php"><i class="fa-solid fa-calendar-plus"></i> Agendar cita</a>
            </nav>
            <form action="cerrar_sesion_paciente.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
                <button class="patient-logout" type="submit"><i class="fa-solid fa-arrow-right-from-bracket"></i><span>Cerrar sesión</span></button>
            </form>
        </header>

        <main class="patient-dashboard">
            <section class="patient-welcome">
                <div>
                    <span class="patient-kicker"><i class="fa-solid fa-sparkles"></i> Tu espacio personal</span>
                    <h1>Hola, <?php echo htmlspecialchars($paciente['nombre'], ENT_QUOTES, 'UTF-8'); ?></h1>
                    <p>Consulta tus datos y mantente al día con el cuidado de tu sonrisa.</p>
                </div>
                <a href="portal_paciente.php" class="btn btn--primary patient-book-button">
                    <i class="fa-solid fa-calendar-plus"></i> Agendar una cita
                </a>
                <div class="patient-welcome__decoration" aria-hidden="true"><i class="fa-solid fa-tooth"></i></div>
            </section>

            <?php if ($flash !== ''): ?>
                <div class="alert alert-success border-0 shadow-sm" role="status">
                    <i class="fa-solid fa-circle-check me-2"></i>
                    <?php echo htmlspecialchars($flash, ENT_QUOTES, 'UTF-8'); ?>
                </div>
            <?php endif; ?>

            <section class="patient-stats" aria-label="Resumen de citas">
                <article class="patient-stat">
                    <span class="patient-stat__icon patient-stat__icon--teal"><i class="fa-solid fa-calendar-check"></i></span>
                    <span><small>Total de citas</small><strong><?php echo $totalCitas; ?></strong></span>
                </article>
                <article class="patient-stat">
                    <span class="patient-stat__icon patient-stat__icon--amber"><i class="fa-solid fa-hourglass-half"></i></span>
                    <span><small>Pendientes</small><strong><?php echo $pendientes; ?></strong></span>
                </article>
                <article class="patient-stat">
                    <span class="patient-stat__icon patient-stat__icon--blue"><i class="fa-solid fa-calendar-day"></i></span>
                    <span><small>Confirmadas</small><strong><?php echo $confirmadas; ?></strong></span>
                </article>
            </section>

            <div class="patient-profile-grid">
                <section class="patient-panel patient-details">
                    <div class="patient-panel__heading">
                        <span class="patient-panel__heading-icon"><i class="fa-solid fa-id-card"></i></span>
                        <div><h2>Mis datos</h2><p>Tu información personal</p></div>
                    </div>
                    <div class="patient-avatar" aria-hidden="true">
                        <?php echo htmlspecialchars(mb_strtoupper(mb_substr($paciente['nombre'], 0, 1, 'UTF-8'), 'UTF-8'), ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                    <h3 class="patient-details__name"><?php echo htmlspecialchars($paciente['nombre'], ENT_QUOTES, 'UTF-8'); ?></h3>
                    <span class="patient-details__label">Paciente DENTIX</span>
                    <dl class="patient-data-list">
                        <div><dt><i class="fa-solid fa-id-card"></i> Cédula</dt><dd><?php echo htmlspecialchars($paciente['cedula'] ?? 'No registrada', ENT_QUOTES, 'UTF-8'); ?></dd></div>
                        <div><dt><i class="fa-solid fa-phone"></i> Teléfono</dt><dd><?php echo htmlspecialchars($paciente['telefono'], ENT_QUOTES, 'UTF-8'); ?></dd></div>
                        <div><dt><i class="fa-solid fa-envelope"></i> Correo</dt><dd><?php echo htmlspecialchars($paciente['email'] ?? 'No registrado', ENT_QUOTES, 'UTF-8'); ?></dd></div>
                        <div><dt><i class="fa-solid fa-cake-candles"></i> Fecha de nacimiento</dt><dd><?php echo htmlspecialchars($paciente['fecha_nac'] ?? 'No registrada', ENT_QUOTES, 'UTF-8'); ?></dd></div>
                        <div><dt><i class="fa-solid fa-notes-medical"></i> Alergias registradas</dt><dd><?php echo htmlspecialchars($paciente['alergias'] ?: 'Ninguna registrada', ENT_QUOTES, 'UTF-8'); ?></dd></div>
                    </dl>
                </section>

                <section class="patient-panel patient-appointments">
                    <div class="patient-panel__heading patient-appointments__heading">
                        <span class="patient-panel__heading-icon"><i class="fa-solid fa-calendar-days"></i></span>
                        <div><h2>Mis citas</h2><p>Agenda e historial de atención</p></div>
                    </div>

                    <?php if ($citas === []): ?>
                        <div class="patient-empty-state">
                            <span><i class="fa-regular fa-calendar"></i></span>
                            <h3>Aún no tienes citas</h3>
                            <p>Cuando agendes una cita, aquí podrás revisar su estado y los detalles de atención.</p>
                            <a href="portal_paciente.php" class="btn btn--primary"><i class="fa-solid fa-calendar-plus me-1"></i> Solicitar mi primera cita</a>
                        </div>
                    <?php else: ?>
                        <div class="patient-appointments-list">
                            <?php foreach ($citas as $cita): ?>
                                <?php
                                $estado = $cita['estado'];
                                $fecha = DateTime::createFromFormat('Y-m-d', $cita['fecha']);
                                $fechaLabel = $fecha ? $fecha->format('d/m/Y') : $cita['fecha'];
                                ?>
                                <article class="patient-appointment">
                                    <div class="patient-appointment__date">
                                        <strong><?php echo htmlspecialchars($fecha ? $fecha->format('d') : '', ENT_QUOTES, 'UTF-8'); ?></strong>
                                        <span><?php echo htmlspecialchars($fecha ? strtoupper($fecha->format('M')) : '', ENT_QUOTES, 'UTF-8'); ?></span>
                                    </div>
                                    <div class="patient-appointment__main">
                                        <div class="patient-appointment__title">
                                            <h3><?php echo htmlspecialchars($cita['servicio_nombre'], ENT_QUOTES, 'UTF-8'); ?></h3>
                                            <span class="patient-status patient-status--<?php echo htmlspecialchars($estado, ENT_QUOTES, 'UTF-8'); ?>">
                                                <?php echo htmlspecialchars($estadoLabels[$estado] ?? ucfirst($estado), ENT_QUOTES, 'UTF-8'); ?>
                                            </span>
                                        </div>
                                        <p><i class="fa-regular fa-calendar"></i> <?php echo htmlspecialchars($fechaLabel, ENT_QUOTES, 'UTF-8'); ?> · <i class="fa-regular fa-clock"></i> <?php echo htmlspecialchars(substr((string) $cita['hora'], 0, 5), ENT_QUOTES, 'UTF-8'); ?></p>
                                        <p>
                                            <i class="fa-solid fa-user-doctor"></i>
                                            <strong>Odontólogo:</strong>
                                            <?php if ($cita['odontologo_nombre'] !== null): ?>
                                                <?php echo htmlspecialchars($cita['odontologo_nombre'], ENT_QUOTES, 'UTF-8'); ?>
                                                <?php if (!empty($cita['odontologo_especialidad'])): ?>
                                                    · <?php echo htmlspecialchars($cita['odontologo_especialidad'], ENT_QUOTES, 'UTF-8'); ?>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                Odontólogo por asignar por recepción
                                            <?php endif; ?>
                                        </p>
                                        <?php if ($estado === 'cancelada' && !empty($cita['motivo_cancelacion'])): ?>
                                            <p class="patient-appointment__note">Motivo de cancelación: <?php echo htmlspecialchars($cita['motivo_cancelacion'], ENT_QUOTES, 'UTF-8'); ?></p>
                                        <?php endif; ?>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </section>
            </div>
        </main>
        <footer class="patient-profile-footer">&copy; 2026 DENTIX · Tu salud oral, en buenas manos.</footer>
    </div>
    <script src="../../assests/js/dentix-i18n.js?v=20261110-cur"></script>
</body>
</html>
