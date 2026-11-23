<?php
// view/odontologo/form_odontologo.php - Formulario de Registro de Odontólogo (Especialidades dinámicas + Consultorios libres RF-039 / RN-19)
require_once __DIR__ . '/../../app/recepcionista_auth.php';
requerir_recepcionista('../recepcionista/lista_recepcionista.php');
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../app/odontologo_catalogos.php';

function generarSlugOdontologo(string $nombre): string
{
    $convertido = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $nombre);
    if ($convertido !== false) {
        $nombre = $convertido;
    }
    $nombre = strtolower($nombre);
    $nombre = preg_replace('/[^a-z0-9]+/', '-', $nombre);
    $nombre = trim((string) $nombre, '-');

    return substr($nombre, 0, 60);
}

$especialidadesDisponibles = obtenerEspecialidadesOdontologia($conexion);
$estadoConsultorios = obtenerEstadoConsultorios($conexion);
$consultoriosLibres = $estadoConsultorios['libres'];
$consultoriosOcupados = $estadoConsultorios['ocupados'];
$totalConsultorios = (int) ($estadoConsultorios['total'] ?? count($estadoConsultorios['todos']));
$nombresConsultoriosLibres = array_map(static fn(array $c): string => $c['nombre'], $consultoriosLibres);

$error = '';
$nombre = '';
$especialidad = '';
$consultorioPre = trim((string) ($_GET['consultorio'] ?? ''));
$consultorio = in_array($consultorioPre, $nombresConsultoriosLibres, true) ? $consultorioPre : '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = formatearNombreOdontologoConTitulo(trim($_POST['nombre'] ?? ''));
    $especialidad = trim($_POST['especialidad'] ?? '');
    $consultorio = trim($_POST['consultorio'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $especialidadValida = false;
    foreach ($especialidadesDisponibles as $espOpt) {
        if (normalizarClaveCatalogo($espOpt) === normalizarClaveCatalogo($especialidad)) {
            $especialidad = $espOpt;
            $especialidadValida = true;
            break;
        }
    }

    if (!validar_csrf_recepcionista()) {
        $error = 'La sesión del formulario venció. Recarga la página e inténtalo de nuevo.';
    } elseif ($nombre === '' || $especialidad === '' || $consultorio === '' || $email === '' || $password === '') {
        $error = 'Por favor, completa los campos obligatorios (Nombre, Especialidad, Consultorio libre, Correo y Contraseña).';
    } elseif (!$especialidadValida) {
        $error = 'Selecciona una especialidad válida de la lista disponible.';
    } elseif (!in_array($consultorio, $nombresConsultoriosLibres, true)) {
        $error = 'El consultorio seleccionado no está disponible o ya se encuentra ocupado por otro odontólogo activo (RN-19).';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Ingresa un correo electrónico válido.';
    } elseif (strlen($password) < 6 || strlen($password) > 72) {
        $error = 'La contraseña debe tener entre 6 y 72 caracteres.';
    } else {
        $slug_publico = generarSlugOdontologo($nombre);

        if ($slug_publico === '') {
            $error = 'No se pudo generar un identificador público válido a partir del nombre.';
        } else {
            try {
                $stmtSlug = $conexion->prepare('SELECT id FROM odontologo WHERE slug_publico = ? LIMIT 1');
                $stmtSlug->execute([$slug_publico]);

                $stmtEmail = $conexion->prepare('SELECT id FROM odontologo WHERE lower(email) = lower(?) LIMIT 1');
                $stmtEmail->execute([$email]);

                $stmtCons = $conexion->prepare(
                    "SELECT nombre FROM odontologo WHERE lower(trim(consultorio)) = lower(trim(?)) AND activo IS TRUE LIMIT 1"
                );
                $stmtCons->execute([$consultorio]);
                $ocupante = $stmtCons->fetchColumn();

                if ($stmtSlug->fetch()) {
                    $error = 'Ya existe un odontólogo con un nombre que genera el mismo identificador público.';
                } elseif ($stmtEmail->fetch()) {
                    $error = 'Ya existe un odontólogo registrado con ese correo electrónico.';
                } elseif ($ocupante) {
                    $error = "El {$consultorio} ya está asignado a {$ocupante}. Selecciona otro consultorio libre.";
                } else {
                    $password_hash = password_hash($password, PASSWORD_DEFAULT);

                    $stmt = $conexion->prepare(
                        'INSERT INTO odontologo (nombre, especialidad, consultorio, slug_publico, email, password_hash, activo)
                         VALUES (?, ?, ?, ?, ?, ?, TRUE)'
                    );
                    $stmt->execute([
                        $nombre,
                        $especialidad,
                        $consultorio,
                        $slug_publico,
                        $email,
                        $password_hash
                    ]);

                    $_SESSION['mensaje'] = "Odontólogo registrado correctamente y asignado al {$consultorio}.";
                    $_SESSION['tipo_mensaje'] = 'exito';
                    header('Location: lista_odontologos.php');
                    exit();
                }
            } catch (PDOException $e) {
                error_log('Error al registrar odontólogo: ' . $e->getMessage());
                $error = 'No se pudo registrar el odontólogo. Verifica que el consultorio y correo no estén en uso.';
            }
        }
    }
}

$csrfToken = token_csrf_recepcionista();
?>
<!DOCTYPE html>
<html lang="es" translate="no" class="notranslate">

<head>
    <meta charset="UTF-8">
    <meta name="google" content="notranslate">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Odontólogo - DENTIX</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@500;600;700;800&family=Sora:wght@700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/admin.css">
    <link rel="stylesheet" href="../../assets/css/index.css">
</head>

<body class="bg-light" style="display: grid; place-items: center; min-height: 100vh; margin: 0; font-family: 'Manrope', sans-serif;">

    <div class="container" style="max-width: 540px; padding: 20px;">
        <div class="card border-0 shadow-lg rounded-4 p-4 p-md-5 bg-white">

            <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
                <div>
                    <span class="badge bg-teal bg-opacity-10 text-teal px-3 py-1 rounded-pill fw-bold mb-2" style="background-color: #ccfbf1; color: #0d9488; font-size: 0.75rem;">
                        <i class="fa-solid fa-circle-dot me-1"></i> ESPECIALISTAS
                    </span>
                    <h3 class="fw-bold text-dark m-0" style="font-family: 'Sora', sans-serif;">Registrar Odontólogo</h3>
                </div>
                <a href="lista_odontologos.php" class="text-muted text-decoration-none small fw-semibold">
                    <i class="fa-solid fa-arrow-left me-1"></i> Volver
                </a>
            </div>

            <?php if ($error !== ''): ?>
                <div class="alert alert-danger py-2 small mb-4 border-0 shadow-sm d-flex align-items-center">
                    <i class="fa-solid fa-triangle-exclamation me-2"></i>
                    <div><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
                </div>
            <?php endif; ?>

            <?php if (count($consultoriosLibres) === 0): ?>
                <div class="alert alert-warning py-2 small mb-4 border-0 shadow-sm d-flex align-items-center">
                    <i class="fa-solid fa-door-closed me-2"></i>
                    <?php if ($totalConsultorios === 0): ?>
                        <div>No hay consultorios configurados en esta sede. Crea uno primero desde el <a href="lista_odontologos.php#panel-consultorios" class="fw-bold text-decoration-underline text-dark">Mapa de Consultorios</a> para registrar un odontólogo.</div>
                    <?php else: ?>
                        <div>Todos los consultorios (<?php echo $totalConsultorios; ?> de <?php echo $totalConsultorios; ?>) están ocupados actualmente. Crea un nuevo consultorio o libera uno desde el <a href="lista_odontologos.php#panel-consultorios" class="fw-bold text-decoration-underline text-dark">Mapa de Consultorios</a> para registrar un nuevo odontólogo.</div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <form action="form_odontologo.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">

                <div class="mb-3">
                    <label for="nombre" class="form-label small fw-semibold text-dark">Nombre Completo</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-user-doctor text-muted"></i></span>
                        <input type="text" class="form-control border-start-0" id="nombre" name="nombre" maxlength="100" value="<?php echo htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8'); ?>" placeholder="Ej. Maik Cardenas o Dra. Ana Torres" required>
                    </div>
                    <small class="text-muted d-block mt-1" id="nombreTituloPreview">Si no escribes <strong>Dr.</strong> o <strong>Dra.</strong>, el sistema lo asignará automáticamente según el nombre.</small>
                </div>

                <div class="mb-3">
                    <label for="especialidad" class="form-label small fw-semibold text-dark">Especialidad Clínica (Servicio que atiende)</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-stethoscope text-muted"></i></span>
                        <select class="form-select border-start-0" id="especialidad" name="especialidad" required>
                            <option value="">-- Seleccionar servicio / especialidad --</option>
                            <?php foreach ($especialidadesDisponibles as $espOpt): ?>
                                <option value="<?php echo htmlspecialchars($espOpt, ENT_QUOTES, 'UTF-8'); ?>" <?php echo ($especialidad !== '' && normalizarClaveCatalogo($especialidad) === normalizarClaveCatalogo($espOpt)) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($espOpt, ENT_QUOTES, 'UTF-8'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <small class="text-muted">Solo se listan los servicios activos de la clínica; Recepción asignará citas de este servicio a este especialista.</small>
                </div>

                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <label for="consultorio" class="form-label small fw-semibold text-dark mb-0">Consultorio Disponible</label>
                        <span class="badge rounded-pill" style="background: #ecfdf5; color: #047857; font-size: 0.7rem;">
                            <?php echo count($consultoriosLibres); ?> libres de <?php echo $totalConsultorios; ?>
                        </span>
                    </div>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-door-open text-muted"></i></span>
                        <select class="form-select border-start-0" id="consultorio" name="consultorio" required <?php echo count($consultoriosLibres) === 0 ? 'disabled' : ''; ?>>
                            <?php if (count($consultoriosLibres) > 0): ?>
                                <option value="">-- Seleccionar consultorio libre --</option>
                                <?php foreach ($consultoriosLibres as $cLibre): ?>
                                    <option value="<?php echo htmlspecialchars($cLibre['nombre'], ENT_QUOTES, 'UTF-8'); ?>" <?php echo ($consultorio === $cLibre['nombre']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($cLibre['nombre'], ENT_QUOTES, 'UTF-8'); ?> (Libre)
                                    </option>
                                <?php endforeach; ?>
                            <?php elseif ($totalConsultorios === 0): ?>
                                <option value="">No hay consultorios configurados en la sede</option>
                            <?php else: ?>
                                <option value="">Todos los consultorios (<?php echo $totalConsultorios; ?> de <?php echo $totalConsultorios; ?>) están ocupados</option>
                            <?php endif; ?>
                        </select>
                    </div>
                    <small class="text-muted">Solo se muestran consultorios libres (los ocupados por otros odontólogos activos aparecen en el Mapa de Consultorios).</small>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label small fw-semibold text-dark">Correo Electrónico (Login)</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-envelope text-muted"></i></span>
                        <input type="email" class="form-control border-start-0" id="email" name="email" maxlength="100" value="<?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?>" placeholder="carlos.perez@clinica.com" required>
                    </div>
                </div>

                <div class="mb-4">
                    <label for="password" class="form-label small fw-semibold text-dark">Contraseña de Acceso</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-lock text-muted"></i></span>
                        <input type="password" class="form-control border-start-0" id="password" name="password" minlength="6" maxlength="72" placeholder="••••••••" required>
                    </div>
                    <small class="text-muted">Mínimo 6 caracteres para ingresar al portal médico.</small>
                </div>

                <button type="submit" class="btn text-white w-100 py-2 fw-bold rounded-pill mb-4 shadow-sm" style="background-color: #0d9488; border: none;" <?php echo count($consultoriosLibres) === 0 ? 'disabled' : ''; ?>>
                    <i class="fa-solid fa-user-plus me-2"></i> Registrar Odontólogo
                </button>

                <div class="text-center pt-3 border-top small">
                    <a href="lista_odontologos.php" class="text-primary fw-bold text-decoration-none d-inline-flex align-items-center gap-1 mb-2">
                        <i class="fa-solid fa-list"></i> Ver odontólogos registrados
                    </a>
                    <br>
                    <a href="../../index.php" class="text-muted text-decoration-none">
                        <i class="fa-solid fa-house me-1"></i> Volver al inicio
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