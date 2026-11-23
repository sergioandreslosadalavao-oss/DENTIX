<?php
require_once __DIR__ . '/../../app/recepcionista_auth.php';
requerir_recepcionista('../recepcionista/lista_recepcionista.php');

// Si se accede directamente con ?id=... (sin pasar por logica_odontologo.php), cargamos el odontólogo aquí.
if (!isset($odontologo) || !is_array($odontologo)) {
    require_once __DIR__ . '/../../config/database.php';

    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    if ($id === false || $id === null || $id <= 0) {
        $_SESSION['mensaje'] = 'Error: el ID del odontólogo no es válido.';
        $_SESSION['tipo_mensaje'] = 'error';
        header('Location: lista_odontologos.php');
        exit;
    }

    try {
        $stmt = $conexion->prepare(
            'SELECT id, nombre, especialidad, consultorio, slug_publico, email, activo
             FROM odontologo
             WHERE id = :id'
        );
        $stmt->execute([':id' => $id]);
        $odontologo = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log('Error al consultar odontólogo en editar_odontologo.php: ' . $e->getMessage());
        $odontologo = false;
    }

    if (!$odontologo) {
        $_SESSION['mensaje'] = 'No se encontró el odontólogo solicitado.';
        $_SESSION['tipo_mensaje'] = 'error';
        header('Location: lista_odontologos.php');
        exit;
    }
}

$mensaje = $_SESSION['mensaje'] ?? null;
$tipo_mensaje = $_SESSION['tipo_mensaje'] ?? 'exito';
unset($_SESSION['mensaje'], $_SESSION['tipo_mensaje']);

if (!isset($conexion)) {
    require_once __DIR__ . '/../../config/database.php';
}
require_once __DIR__ . '/../../app/odontologo_catalogos.php';

$especialidadesDisponibles = obtenerEspecialidadesOdontologia($conexion);
$estadoConsultorios = obtenerEstadoConsultorios($conexion, (int) $odontologo['id']);
$consultoriosDisponiblesEdicion = $estadoConsultorios['libres'];
$consultorioActual = trim((string) ($odontologo['consultorio'] ?? ''));

$csrfToken = token_csrf_recepcionista();
?>
<!DOCTYPE html>
<html lang="es" translate="no" class="notranslate">
<head>
    <meta charset="UTF-8">
    <meta name="google" content="notranslate">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Odontólogo - DENTIX</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@500;600;700;800&family=Sora:wght@700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/admin.css">
    <link rel="stylesheet" href="../../assets/css/index.css">
</head>
<body class="admin-body" style="display: grid; place-items: center; min-height: 100vh; padding: 30px 15px;">

    <div class="container" style="max-width: 580px;">
        <div class="form-card shadow-lg p-4 p-md-5 border-0 rounded-4 bg-white">
            
            <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
                <div>
                    <span class="eyebrow mb-1 text-primary fw-bold" style="font-size: 0.75rem; letter-spacing: 0.05em; text-transform: uppercase;">Gestión de Especialistas</span>
                    <h2 class="fw-bold text-dark mb-0 fs-3" style="font-family: 'Sora', sans-serif;">
                        <i class="fa-solid fa-user-doctor text-primary me-2"></i> Editar Odontólogo
                    </h2>
                </div>
                <a href="lista_odontologos.php" class="btn btn-outline-secondary btn-sm rounded-pill px-3 fw-semibold">
                    <i class="fa-solid fa-arrow-left me-1"></i> Volver
                </a>
            </div>

            <?php if ($mensaje !== null): ?>
                <div class="alert <?= $tipo_mensaje === 'error' ? 'alert-danger' : 'alert-success' ?> py-2 mb-4 small d-flex align-items-center rounded-3" role="alert">
                    <i class="fa-solid <?= $tipo_mensaje === 'error' ? 'fa-triangle-exclamation' : 'fa-circle-check' ?> me-2 fs-5"></i>
                    <div><?= htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8') ?></div>
                </div>
            <?php endif; ?>

            <form action="../../app/logica_odontologo.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="accion" value="actualizar">
                <input type="hidden" name="id" value="<?= (int) $odontologo['id'] ?>">

                <div class="mb-3">
                    <label for="nombre" class="form-label fw-semibold small text-dark">Nombre Completo</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-user-nurse text-muted"></i></span>
                        <input
                            type="text"
                            class="form-control border-start-0"
                            id="nombre"
                            name="nombre"
                            maxlength="100"
                            value="<?= htmlspecialchars($odontologo['nombre'], ENT_QUOTES, 'UTF-8') ?>"
                            required
                            placeholder="Ej. Maik Cardenas o Dra. Ana Torres"
                        >
                    </div>
                    <small class="text-muted d-block mt-1" id="nombreTituloPreview">Si no escribes <strong>Dr.</strong> o <strong>Dra.</strong>, el sistema lo asignará automáticamente según el nombre.</small>
                </div>

                <div class="mb-3">
                    <label for="especialidad" class="form-label fw-semibold small text-dark">Especialidad Clínica (Servicio que atiende)</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-stethoscope text-muted"></i></span>
                        <select class="form-select border-start-0" id="especialidad" name="especialidad" required>
                            <option value="">-- Seleccionar servicio / especialidad --</option>
                            <?php foreach ($especialidadesDisponibles as $espOpt): ?>
                                <option value="<?= htmlspecialchars($espOpt, ENT_QUOTES, 'UTF-8') ?>" <?= normalizarClaveCatalogo((string) ($odontologo['especialidad'] ?? '')) === normalizarClaveCatalogo($espOpt) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($espOpt, ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <small class="text-muted">Solo se listan los servicios activos de la clínica; Recepción asignará citas de este servicio a este especialista.</small>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label for="consultorio" class="form-label fw-semibold small text-dark">Consultorio / Espacio</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-door-closed text-muted"></i></span>
                            <select class="form-select border-start-0" id="consultorio" name="consultorio" required>
                                <option value="">-- Seleccionar consultorio libre --</option>
                                <?php
                                $encontradoActual = false;
                                foreach ($consultoriosDisponiblesEdicion as $cDisp):
                                    $esActual = ($consultorioActual !== '' && normalizarClaveCatalogo($consultorioActual) === normalizarClaveCatalogo($cDisp['nombre']));
                                    if ($esActual) $encontradoActual = true;
                                ?>
                                    <option value="<?= htmlspecialchars($cDisp['nombre'], ENT_QUOTES, 'UTF-8') ?>" <?= $esActual ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($cDisp['nombre'], ENT_QUOTES, 'UTF-8') ?><?= $esActual ? ' (Actual)' : ' (Libre)' ?>
                                    </option>
                                <?php endforeach; ?>
                                <?php if ($consultorioActual !== '' && !$encontradoActual): ?>
                                    <option value="<?= htmlspecialchars($consultorioActual, ENT_QUOTES, 'UTF-8') ?>" selected>
                                        <?= htmlspecialchars($consultorioActual, ENT_QUOTES, 'UTF-8') ?> (Actual)
                                    </option>
                                <?php endif; ?>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label for="activo" class="form-label fw-semibold small text-dark">Estado de Cuenta</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-toggle-on text-muted"></i></span>
                            <select class="form-select border-start-0" id="activo" name="activo">
                                <option value="1" <?= !empty($odontologo['activo']) ? 'selected' : '' ?>>Activo (Habilitado)</option>
                                <option value="0" <?= empty($odontologo['activo']) ? 'selected' : '' ?>>Inactivo (Deshabilitado)</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label fw-semibold small text-dark">Correo Electrónico (Acceso)</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-envelope text-muted"></i></span>
                        <input
                            type="email"
                            class="form-control border-start-0"
                            id="email"
                            name="email"
                            maxlength="100"
                            value="<?= htmlspecialchars($odontologo['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                            required
                            placeholder="especialista@clinica.com"
                        >
                    </div>
                </div>

                <div class="p-3 bg-light rounded-3 border mb-4">
                    <div class="d-flex align-items-center text-muted small mb-1">
                        <i class="fa-solid fa-link me-2 text-primary"></i>
                        <span class="fw-semibold text-dark">Enlace público (Slug):</span>
                        <span class="badge bg-white text-dark border ms-2 font-monospace"><?= htmlspecialchars($odontologo['slug_publico'], ENT_QUOTES, 'UTF-8') ?></span>
                    </div>
                    <small class="text-muted d-block" style="font-size: 0.8rem;">
                        <i class="fa-solid fa-circle-info me-1"></i> El enlace se actualiza de forma automática si modificas el nombre del especialista.
                    </small>
                </div>

                <div class="d-grid gap-2 pt-1">
                    <button type="submit" class="btn btn--primary py-2 fw-bold rounded-3 shadow-sm">
                        <i class="fa-solid fa-floppy-disk me-2"></i> Guardar cambios
                    </button>
                    <a href="lista_odontologos.php" class="btn btn-light py-2 text-muted fw-semibold rounded-3 text-center">
                        Cancelar y volver al listado
                    </a>
                </div>
            </form>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        (() => {
            const inputNombre = document.getElementById('nombre');
            const preview = document.getElementById('nombreTituloPreview');
            if (!inputNombre || !preview) return;

            const femSinA = new Set(['abril','ailin','amparo','angie','araceli','astrid','beatriz','belen','carmen','carol','celeste','chloe','cindy','consuelo','daisy','deisy','denisse','dolores','edith','elizabeth','esther','evelyn','flor','genesis','guadalupe','heidy','ingrid','irene','iris','isabel','janeth','jennifer','jenny','judith','karen','katerin','katherine','kathy','kelly','kimberly','lady','leidy','lesly','lilibeth','liz','lizeth','luz','magaly','maribel','marisol','marlene','mary','mayra','mayerly','mercedes','michelle','milagros','miriam','naomi','nataly','nathaly','nancy','nicole','nieves','noemi','pilar','rachel','raquel','rocio','rosario','ruth','salome','sandy','sarai','sharick','sharon','soledad','sol','stephanie','sully','trinidad','wendy','yaneth','yudy','yuly','zoe']);
            const mascConA = new Set(['borja','bautista','ezra','joshua','luca','mustafa','nikola']);

            const normalizar = (s) => s.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase().trim();
            const inferirNombre = (raw) => {
                const limpio = raw.replace(/\s+/g, ' ').trim();
                if (!limpio) return '';
                const match = limpio.match(/^(dra?)\.?\s+(.+)$/i);
                if (match) {
                    const pref = match[1].toLowerCase() === 'dra' ? 'Dra.' : 'Dr.';
                    return `${pref} ${match[2].trim()}`;
                }
                const primer = normalizar(limpio.split(' ')[0] || '');
                const esFem = femSinA.has(primer) || (!mascConA.has(primer) && primer.endsWith('a'));
                return `${esFem ? 'Dra.' : 'Dr.'} ${limpio}`;
            };

            const actualizarPreview = () => {
                const res = inferirNombre(inputNombre.value);
                if (!res) {
                    preview.innerHTML = 'Si no escribes <strong>Dr.</strong> o <strong>Dra.</strong>, el sistema lo asignará automáticamente según el nombre.';
                    return;
                }
                preview.innerHTML = 'Se guardará como: <strong class="text-teal" style="color:#0d9488;"></strong>';
                preview.querySelector('strong').textContent = res;
            };

            inputNombre.addEventListener('input', actualizarPreview);
            inputNombre.addEventListener('blur', () => {
                const res = inferirNombre(inputNombre.value);
                if (res) {
                    inputNombre.value = res;
                    actualizarPreview();
                }
            });
            actualizarPreview();
        })();
    </script>
    <script src="../../assets/js/dentix-i18n.js?v=20261120-i18nfix"></script>
</body>
</html>
