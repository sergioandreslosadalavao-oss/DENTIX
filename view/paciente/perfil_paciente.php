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

$catalogoAlergias = [
    'Ninguna registrada' => 'Ninguna alergia conocida',
    'Penicilina / Amoxicilina (Antibióticos)' => 'Penicilina / Amoxicilina (Antibióticos)',
    'Anestesia local (Lidocaína / Articaína)' => 'Anestesia local (Lidocaína / Articaína)',
    'Ibuprofeno / AINEs / Aspirina (Analgésicos)' => 'Ibuprofeno / AINEs / Aspirina (Analgésicos)',
    'Látex (Guantes o insumos)' => 'Látex (Guantes o insumos)',
    'Yodo / Clorhexidina (Antisépticos)' => 'Yodo / Clorhexidina (Antisépticos)',
    'Metales dentales (Níquel / Cromo / Cobalto)' => 'Metales dentales (Níquel / Cromo / Cobalto)',
    'Otra' => 'Otra alergia (Escribir manualmente...)',
];

$errorPerfil = $_SESSION['paciente_error'] ?? '';
unset($_SESSION['paciente_error']);
$abrirModalEditar = (($_GET['editar'] ?? '') === '1');

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
<html lang="es" translate="no" class="notranslate">
<head>
    <meta charset="UTF-8">
    <meta name="google" content="notranslate">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi perfil - DENTIX</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@500;600;700;800&family=Sora:wght@700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/admin.css">
    <link rel="stylesheet" href="../../assets/css/paciente.css?v=20261110-vivid">
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

            <?php if ($errorPerfil !== ''): ?>
                <div class="alert alert-danger border-0 shadow-sm" role="alert">
                    <i class="fa-solid fa-triangle-exclamation me-2"></i>
                    <?php echo htmlspecialchars($errorPerfil, ENT_QUOTES, 'UTF-8'); ?>
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
                    <div class="patient-panel__heading d-flex align-items-center justify-content-between gap-2 flex-wrap">
                        <div class="d-flex align-items-center gap-3">
                            <span class="patient-panel__heading-icon"><i class="fa-solid fa-id-card"></i></span>
                            <div><h2>Mis datos</h2><p>Tu información personal</p></div>
                        </div>
                        <button
                            type="button"
                            id="btnAbrirEditarPerfil"
                            class="btn btn-sm py-1 px-3"
                            data-bs-toggle="modal"
                            data-bs-target="#modalEditarPerfilPaciente"
                            style="font-size: 0.78rem; font-weight: 700; border-radius: 999px; background: linear-gradient(135deg, #0d9488, #0f766e); color: #ffffff; border: none; box-shadow: 0 6px 14px rgba(13, 148, 136, 0.24);"
                        >
                            <i class="fa-solid fa-user-pen me-1"></i> Editar mis datos
                        </button>
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
                        <div>
                            <dt><i class="fa-solid fa-notes-medical"></i> Alergias registradas</dt>
                            <dd id="alergiasActualesText"><?php echo htmlspecialchars($paciente['alergias'] ?: 'Ninguna registrada', ENT_QUOTES, 'UTF-8'); ?></dd>
                        </div>
                    </dl>

                    <?php
                    $alergiaActual = trim((string) ($paciente['alergias'] ?? ''));
                    if ($alergiaActual === '' || mb_strtolower($alergiaActual, 'UTF-8') === 'ninguna') {
                        $alergiaSelectActual = 'Ninguna registrada';
                        $alergiaManualActual = '';
                    } elseif (array_key_exists($alergiaActual, $catalogoAlergias)) {
                        $alergiaSelectActual = $alergiaActual;
                        $alergiaManualActual = '';
                    } else {
                        $alergiaSelectActual = 'Otra';
                        $alergiaManualActual = $alergiaActual;
                    }
                    ?>
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

    <!-- MODAL DE EDICIÓN INTEGRAL DE DATOS DEL PACIENTE (RF-037) -->
    <div class="modal fade" id="modalEditarPerfilPaciente" tabindex="-1" aria-labelledby="modalEditarPerfilLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="modal-header border-0 text-white px-4 py-3" style="background: linear-gradient(135deg, #063d3a 0%, #0d9488 100%);">
                    <div class="d-flex align-items-center gap-3">
                        <span style="width: 42px; height: 42px; border-radius: 12px; background: rgba(255,255,255,0.16); display: grid; place-items: center; font-size: 1.15rem;">
                            <i class="fa-solid fa-user-pen"></i>
                        </span>
                        <div>
                            <h5 class="modal-title fw-bold mb-0" id="modalEditarPerfilLabel" style="font-family: 'Sora', sans-serif;">Editar mi información personal</h5>
                            <small style="color: #ccfbf1;">Tus cambios se sincronizan automáticamente con recepción y tu odontólogo.</small>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <form action="../../app/logica_paciente.php" method="POST" id="formEditarPerfilCompleto">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
                    <input type="hidden" name="accion" value="actualizar_perfil_propio">

                    <div class="modal-body p-4" style="background: #f8fdfb;">
                        <?php if ($errorPerfil !== '' && $abrirModalEditar): ?>
                            <div class="alert alert-danger py-2 small border-0 shadow-sm mb-3 d-flex align-items-center">
                                <i class="fa-solid fa-triangle-exclamation me-2"></i>
                                <div><?php echo htmlspecialchars($errorPerfil, ENT_QUOTES, 'UTF-8'); ?></div>
                            </div>
                        <?php endif; ?>

                        <div class="row g-3">
                            <div class="col-md-7">
                                <label for="edit_nombre_paciente" class="form-label fw-bold small" style="color: #074743;">
                                    <i class="fa-solid fa-user me-1 text-teal"></i> Nombre completo
                                </label>
                                <input
                                    type="text"
                                    class="form-control rounded-3"
                                    id="edit_nombre_paciente"
                                    name="nombre"
                                    required
                                    maxlength="100"
                                    value="<?php echo htmlspecialchars($paciente['nombre'], ENT_QUOTES, 'UTF-8'); ?>"
                                    placeholder="Ej. Andrea Morales o María de la Cruz"
                                >
                                <div class="form-text small">Formato Nombre Propio (se permiten conectores como <em>de, del, la</em> y apellidos compuestos).</div>
                            </div>

                            <div class="col-md-5">
                                <label for="edit_cedula_readonly" class="form-label fw-bold small" style="color: #074743;">
                                    <i class="fa-solid fa-lock me-1 text-muted"></i> Cédula / Documento
                                </label>
                                <input
                                    type="text"
                                    class="form-control rounded-3 bg-light text-muted fw-semibold"
                                    id="edit_cedula_readonly"
                                    value="<?php echo htmlspecialchars($paciente['cedula'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                    readonly
                                >
                                <div class="form-text small">Identificador único de acceso (no editable).</div>
                            </div>

                            <div class="col-md-6">
                                <label for="edit_telefono_paciente" class="form-label fw-bold small" style="color: #074743;">
                                    <i class="fa-solid fa-phone me-1 text-teal"></i> Teléfono de contacto
                                </label>
                                <input
                                    type="tel"
                                    class="form-control rounded-3"
                                    id="edit_telefono_paciente"
                                    name="telefono"
                                    required
                                    maxlength="20"
                                    value="<?php echo htmlspecialchars($paciente['telefono'], ENT_QUOTES, 'UTF-8'); ?>"
                                    placeholder="3001234567"
                                >
                            </div>

                            <div class="col-md-6">
                                <label for="edit_email_paciente" class="form-label fw-bold small" style="color: #074743;">
                                    <i class="fa-solid fa-envelope me-1 text-teal"></i> Correo electrónico
                                </label>
                                <input
                                    type="email"
                                    class="form-control rounded-3"
                                    id="edit_email_paciente"
                                    name="email"
                                    maxlength="100"
                                    value="<?php echo htmlspecialchars($paciente['email'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                    placeholder="correo@ejemplo.com"
                                >
                            </div>

                            <div class="col-md-5">
                                <label for="edit_fecha_nac_paciente" class="form-label fw-bold small" style="color: #074743;">
                                    <i class="fa-solid fa-cake-candles me-1 text-teal"></i> Fecha de nacimiento
                                </label>
                                <input
                                    type="date"
                                    class="form-control rounded-3"
                                    id="edit_fecha_nac_paciente"
                                    name="fecha_nac"
                                    max="<?php echo date('Y-m-d'); ?>"
                                    value="<?php echo htmlspecialchars($paciente['fecha_nac'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                >
                            </div>

                            <div class="col-md-7">
                                <label for="edit_alergia_select" class="form-label fw-bold small" style="color: #074743;">
                                    <i class="fa-solid fa-notes-medical me-1 text-danger"></i> Alergias o alertas médicas
                                </label>
                                <select class="form-select rounded-3 mb-2" id="edit_alergia_select" name="alergia_select">
                                    <?php foreach ($catalogoAlergias as $valor => $etiqueta): ?>
                                        <option value="<?php echo htmlspecialchars($valor, ENT_QUOTES, 'UTF-8'); ?>" <?php echo ($alergiaSelectActual === $valor) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($etiqueta, ENT_QUOTES, 'UTF-8'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <input
                                    type="text"
                                    class="form-control form-control-sm rounded-3"
                                    id="edit_alergia_manual"
                                    name="alergia_manual"
                                    maxlength="90"
                                    placeholder="Detalle adicional o alergia específica (opcional)..."
                                    value="<?php echo htmlspecialchars($alergiaManualActual, ENT_QUOTES, 'UTF-8'); ?>"
                                >
                            </div>

                            <div class="col-12 mt-2">
                                <div class="p-3 rounded-3 border" style="background: #ffffff; border-color: rgba(13, 148, 136, 0.2) !important;">
                                    <div class="fw-bold small mb-2" style="color: #074743;">
                                        <i class="fa-solid fa-shield-halved me-1"></i> Seguridad: Cambiar contraseña (Opcional)
                                    </div>
                                    <div class="row g-2">
                                        <div class="col-md-6">
                                            <input
                                                type="password"
                                                class="form-control form-control-sm rounded-3"
                                                id="edit_nueva_password"
                                                name="nueva_password"
                                                minlength="8"
                                                maxlength="72"
                                                autocomplete="new-password"
                                                placeholder="Nueva contraseña (dejar vacío para mantener)"
                                            >
                                        </div>
                                        <div class="col-md-6">
                                            <input
                                                type="password"
                                                class="form-control form-control-sm rounded-3"
                                                id="edit_confirmar_password"
                                                name="confirmar_password"
                                                minlength="8"
                                                maxlength="72"
                                                autocomplete="new-password"
                                                placeholder="Confirmar nueva contraseña"
                                            >
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer border-0 px-4 py-3 bg-white d-flex justify-content-between align-items-center">
                        <span class="small text-muted"><i class="fa-solid fa-rotate me-1" style="color: #0d9488;"></i> Sincronización automática con Recepción</span>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-light rounded-pill px-3 fw-semibold" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn--primary rounded-pill px-4 fw-bold">
                                <i class="fa-solid fa-floppy-disk me-1"></i> Guardar cambios
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        (() => {
            const syncManualRequirement = (sel, inp) => {
                if (!sel || !inp) return;
                inp.required = (sel.value === 'Otra');
            };

            // Validación y auto-formateo del modal de edición completa de perfil (datos + alergias)
            const editNombre = document.getElementById('edit_nombre_paciente');
            const editAlergiaSel = document.getElementById('edit_alergia_select');
            const editAlergiaMan = document.getElementById('edit_alergia_manual');
            const conectores = new Set(['de', 'del', 'la', 'las', 'los', 'y', 'da', 'do', 'dos']);

            const formatearNombrePropio = (valor) => {
                const limpio = valor.trim().replace(/\s+/g, ' ');
                if (!limpio) return '';
                const partes = limpio.split(' ');
                return partes.map((palabra, idx) => {
                    const lower = palabra.toLowerCase();
                    if (idx > 0 && idx < partes.length - 1 && conectores.has(lower)) {
                        return lower;
                    }
                    if (/^(mc|mac)[a-záéíóúüñ]+$/i.test(palabra)) {
                        const pref = palabra.slice(0, palabra.toLowerCase().startsWith('mac') ? 3 : 2);
                        const rest = palabra.slice(pref.length);
                        return pref.charAt(0).toUpperCase() + pref.slice(1).toLowerCase() +
                            rest.charAt(0).toUpperCase() + rest.slice(1).toLowerCase();
                    }
                    return palabra.split(/(['-])/).map((seg) => {
                        if (seg === "'" || seg === '-' || !seg) return seg;
                        return seg.charAt(0).toUpperCase() + seg.slice(1).toLowerCase();
                    }).join('');
                }).join(' ');
            };

            if (editNombre) {
                editNombre.addEventListener('blur', () => {
                    editNombre.value = formatearNombrePropio(editNombre.value);
                });
            }

            if (editAlergiaSel) {
                editAlergiaSel.addEventListener('change', () => {
                    syncManualRequirement(editAlergiaSel, editAlergiaMan);
                    if (editAlergiaSel.value === 'Otra' && editAlergiaMan) editAlergiaMan.focus();
                });
                syncManualRequirement(editAlergiaSel, editAlergiaMan);
            }

            <?php if ($abrirModalEditar): ?>
            const modalEl = document.getElementById('modalEditarPerfilPaciente');
            if (modalEl && window.bootstrap) {
                new bootstrap.Modal(modalEl).show();
            }
            <?php endif; ?>
        })();
    </script>
    <script src="../../assets/js/dentix-i18n.js?v=20261120-i18nfix"></script>
</body>
</html>
