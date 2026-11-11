<?php
// form_paciente.php - Registro de Nuevo Paciente con Ventana de Términos y Condiciones y Lectura Obligatoria
require_once __DIR__ . '/../../app/paciente_auth.php';
iniciar_sesion_paciente();
require_once __DIR__ . '/../../config/database.php';

$modoAdmin = (($_GET['desde'] ?? $_POST['desde'] ?? '') === 'admin') && isset($_SESSION['recepcionista_id']);

if (!$modoAdmin && paciente_autenticado()) {
    header('Location: perfil_paciente.php');
    exit;
}

$error = '';
$csrfToken = token_csrf_paciente();

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

$nombreVal = '';
$cedulaVal = '';
$telefonoVal = '';
$correoVal = '';
$fechaNacVal = '';
$alergiaSelectVal = 'Ninguna registrada';
$alergiaManualVal = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = is_string($_POST['nombre'] ?? null) ? trim($_POST['nombre']) : '';
    $cedula = is_string($_POST['cedula'] ?? null) ? trim($_POST['cedula']) : '';
    $telefono = is_string($_POST['telefono'] ?? null) ? trim($_POST['telefono']) : '';
    $correo = is_string($_POST['correo'] ?? null) ? trim($_POST['correo']) : '';
    $fechaNac = is_string($_POST['fecha_nac'] ?? null) ? trim($_POST['fecha_nac']) : '';
    $alergiaSelect = is_string($_POST['alergia_select'] ?? null) ? trim($_POST['alergia_select']) : 'Ninguna registrada';
    $alergiaManual = is_string($_POST['alergia_manual'] ?? null) ? trim($_POST['alergia_manual']) : '';
    $password = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';
    $passwordConfirmacion = is_string($_POST['password_confirmacion'] ?? null)
        ? $_POST['password_confirmacion']
        : '';
    $aceptoTerminos = ($_POST['acepto_terminos'] ?? '') === '1';

    $nombreVal = $nombre;
    $cedulaVal = $cedula;
    $telefonoVal = $telefono;
    $correoVal = $correo;
    $fechaNacVal = $fechaNac;
    $alergiaSelectVal = array_key_exists($alergiaSelect, $catalogoAlergias) ? $alergiaSelect : 'Otra';
    $alergiaManualVal = $alergiaManual;

    // Construir valor final de alergias para guardar en la BD (VARCHAR 120)
    if ($alergiaSelect === 'Otra') {
        $alergiasFinal = $alergiaManual;
    } elseif ($alergiaManual !== '' && $alergiaSelect !== 'Ninguna registrada') {
        $alergiasFinal = $alergiaSelect . ' - ' . $alergiaManual;
    } elseif ($alergiaManual !== '' && $alergiaSelect === 'Ninguna registrada') {
        $alergiasFinal = $alergiaManual;
    } else {
        $alergiasFinal = $alergiaSelect !== '' ? $alergiaSelect : 'Ninguna registrada';
    }
    $alergiasFinal = mb_substr($alergiasFinal, 0, 120, 'UTF-8');

    // Validar fecha de nacimiento
    $fechaObj = DateTimeImmutable::createFromFormat('!Y-m-d', $fechaNac);
    $fechaNacValida = $fechaObj !== false
        && $fechaObj->format('Y-m-d') === $fechaNac
        && $fechaNac <= date('Y-m-d')
        && $fechaNac >= '1900-01-01';

    // Validar que cada palabra del nombre empiece con mayúscula seguida de minúsculas (ej. Juan Steven)
    $nombreFormatoValido = preg_match('/^[A-ZÁÉÍÓÚÜÑ][a-záéíóúüñ]+(?: [A-ZÁÉÍÓÚÜÑ][a-záéíóúüñ]+)*$/u', $nombre) === 1;

    if (!validar_csrf_paciente()) {
        $error = 'La sesión del formulario venció. Recarga la página e inténtalo de nuevo.';
    } elseif (!$nombreFormatoValido) {
        $error = 'Cada palabra del nombre debe empezar con mayúscula seguida de minúsculas (ejemplo: Juan Steven). No se permite todo en mayúsculas ni todo en minúsculas.';
    } elseif (
        $nombre === ''
        || mb_strlen($nombre, 'UTF-8') > 60
        || $cedula === ''
        || mb_strlen($cedula, 'UTF-8') > 20
        || $telefono === ''
        || mb_strlen($telefono, 'UTF-8') > 15
        || !filter_var($correo, FILTER_VALIDATE_EMAIL)
        || mb_strlen($correo, 'UTF-8') > 80
        || !$fechaNacValida
        || $password === ''
    ) {
        $error = 'Completa todos los campos correctamente (incluyendo una fecha de nacimiento válida).';
    } elseif ($alergiaSelect === 'Otra' && $alergiaManual === '') {
        $error = 'Por favor escribe cuál es tu alergia en el campo de texto.';
    } elseif (strlen($password) < 8 || strlen($password) > 72) {
        $error = 'La contraseña debe tener entre 8 y 72 caracteres.';
    } elseif ($password !== $passwordConfirmacion) {
        $error = 'Las contraseñas no coinciden.';
    } elseif (!$aceptoTerminos) {
        $error = 'Debes desplegar las 5 cláusulas en "Ver más..." y aceptar los Términos y Condiciones para crear tu cuenta.';
    } else {
        try {
            $check = $conexion->prepare(
                'SELECT id, nombre, telefono, email, password_hash
                 FROM paciente
                 WHERE cedula = ?
                 LIMIT 1'
            );
            $check->execute([$cedula]);

            $pacienteExistente = $check->fetch();
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);

            if ($pacienteExistente && !empty($pacienteExistente['password_hash'])) {
                $error = 'Ya existe una cuenta con esa cédula. Inicia sesión.';
            } elseif ($pacienteExistente) {
                $datosCoinciden = hash_equals((string) $pacienteExistente['telefono'], $telefono)
                    && strtolower((string) $pacienteExistente['email']) === strtolower($correo)
                    && mb_strtolower((string) $pacienteExistente['nombre'], 'UTF-8') === mb_strtolower($nombre, 'UTF-8');

                if (!$datosCoinciden) {
                    $error = 'No pudimos verificar tus datos. Revisa el teléfono y correo registrados o comunícate con recepción.';
                } else {
                    $stmt = $conexion->prepare(
                        'UPDATE paciente
                         SET password_hash = ?, fecha_nac = ?, alergias = ?
                         WHERE id = ? AND password_hash IS NULL'
                    );
                    $stmt->execute([$passwordHash, $fechaNac, $alergiasFinal, $pacienteExistente['id']]);
                    if ($stmt->rowCount() === 1) {
                        $pacienteId = $pacienteExistente['id'];
                    } else {
                        $error = 'La cuenta ya fue activada. Inicia sesión con tu cédula y contraseña.';
                    }
                }
            } else {
                $stmt = $conexion->prepare(
                    'INSERT INTO paciente (nombre, cedula, telefono, email, fecha_nac, alergias, password_hash)
                     VALUES (?, ?, ?, ?, ?, ?, ?)
                     RETURNING id'
                );
                $stmt->execute([$nombre, $cedula, $telefono, $correo, $fechaNac, $alergiasFinal, $passwordHash]);
                $pacienteId = $stmt->fetchColumn();
            }

            if ($error === '') {
                if ($modoAdmin) {
                    $_SESSION['mensaje'] = 'Paciente registrado correctamente con consentimiento de datos informado.';
                    $_SESSION['tipo_mensaje'] = 'exito';
                    header('Location: lista_pacientes.php');
                    exit;
                }
                session_regenerate_id(true);
                $_SESSION['paciente_id'] = (string) $pacienteId;
                $_SESSION['paciente_nombre'] = $nombre;
                $_SESSION['paciente_flash'] = '¡Bienvenido(a) a DENTIX! Tu cuenta e historia clínica inicial han sido creadas bajo nuestra Política de Protección de Datos de Salud.';
                header('Location: perfil_paciente.php');
                exit;
            }
        } catch (PDOException $e) {
            error_log('Error al registrar cuenta de paciente: ' . $e->getMessage());
            $error = 'No se pudo crear tu cuenta. Verifica que el nombre inicie cada palabra con mayúscula (ej. Juan Steven) y que los datos sean válidos.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Nuevo Paciente - DENTIX</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@500;600;700;800&family=Sora:wght@700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

    <link rel="stylesheet" href="../../assests/css/admin.css">
    <link rel="stylesheet" href="../../assests/css/index.css">
    <link rel="stylesheet" href="../../assests/css/paciente.css?v=20261110-terms2">
    <style>
        .patient-register-card {
            width: min(100%, 600px);
            padding: clamp(26px, 4.5vw, 42px);
        }
        .register-section-tag {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            margin: 6px 0 12px;
            padding: 5px 12px;
            border-radius: 999px;
            background: linear-gradient(135deg, #e5faf3, #d2f6eb);
            border: 1px solid rgba(16, 162, 148, 0.22);
            color: #076c63;
            font-size: 0.72rem;
            font-weight: 800;
            letter-spacing: 0.06em;
            text-transform: uppercase;
        }
        .register-field-group .input-group-text {
            background: #f0fbf8;
            border-color: rgba(7, 45, 48, 0.14);
            color: #0a8f82;
            width: 44px;
            justify-content: center;
        }
        .register-field-group .form-control,
        .register-field-group .form-select {
            border-color: rgba(7, 45, 48, 0.14);
            font-size: 0.9rem;
        }
        .register-field-group .form-control:focus,
        .register-field-group .form-select:focus {
            border-color: #10a294;
            box-shadow: 0 0 0 0.2rem rgba(16, 162, 148, 0.15);
        }
        .allergy-box {
            padding: 16px;
            border-radius: 18px;
            border: 1px solid rgba(16, 162, 148, 0.22);
            background: linear-gradient(135deg, #f3fcf9 0%, #ffffff 100%);
        }
        .allergy-chips {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            margin-top: 10px;
        }
        .allergy-chip {
            border: 1px solid rgba(16, 162, 148, 0.24);
            border-radius: 999px;
            background: #ffffff;
            color: #0a6e65;
            padding: 4px 11px;
            font-size: 0.74rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .allergy-chip:hover,
        .allergy-chip.is-active {
            background: linear-gradient(135deg, #0a7a70, #10a294);
            color: #ffffff;
            border-color: transparent;
        }

        /* =====================================================
           Ventana Modal Profesional: Términos y Tratamiento de Datos
           ===================================================== */
        body.is-terms-open {
            overflow: hidden;
        }
        .terms-modal {
            position: fixed;
            inset: 0;
            z-index: 1050;
            display: grid;
            place-items: center;
            padding: clamp(12px, 2.5vw, 24px);
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
            transition: opacity 0.3s ease, visibility 0.3s ease;
        }
        .terms-modal.is-open {
            opacity: 1;
            visibility: visible;
            pointer-events: auto;
        }
        .terms-modal__backdrop {
            position: absolute;
            inset: 0;
            background: rgba(5, 34, 36, 0.68);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }
        .terms-modal__dialog {
            position: relative;
            z-index: 1;
            display: flex;
            flex-direction: column;
            width: min(820px, 100%);
            max-height: min(92vh, 840px);
            border-radius: 28px;
            border: 1px solid rgba(255, 255, 255, 0.9);
            background: linear-gradient(170deg, #ffffff 0%, #f4fbf8 100%);
            box-shadow: 0 40px 95px -24px rgba(4, 28, 30, 0.72);
            overflow: hidden;
            transform: translateY(18px) scale(0.97);
            transition: transform 0.32s cubic-bezier(0.22, 1, 0.36, 1);
        }
        .terms-modal.is-open .terms-modal__dialog {
            transform: translateY(0) scale(1);
        }
        .terms-modal__header {
            position: relative;
            padding: 22px 28px 18px;
            background:
                radial-gradient(circle at 90% 15%, rgba(63, 217, 197, 0.35) 0, transparent 18rem),
                linear-gradient(125deg, #063c3c 0%, #0a6d65 55%, #10a294 100%);
            color: #ffffff;
        }
        .terms-modal__badge {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 5px 12px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.14);
            border: 1px solid rgba(255, 255, 255, 0.24);
            color: #c9f5ea;
            font-size: 0.68rem;
            font-weight: 800;
            letter-spacing: 0.09em;
            text-transform: uppercase;
            margin-bottom: 8px;
        }
        .terms-modal__header h2 {
            font-family: "Sora", sans-serif;
            font-size: clamp(1.22rem, 2.2vw, 1.5rem);
            font-weight: 800;
            color: #ffffff;
            margin: 0 0 5px;
        }
        .terms-modal__header p {
            margin: 0;
            font-size: 0.82rem;
            color: rgba(233, 251, 246, 0.9);
            line-height: 1.48;
        }
        .terms-applicant-bar {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            padding: 10px 28px;
            background: #e7faf4;
            border-bottom: 1px solid rgba(16, 162, 148, 0.18);
            font-size: 0.79rem;
            color: #074743;
        }
        .terms-applicant-bar strong {
            color: #052e2b;
            font-weight: 800;
        }

        .terms-modal__body {
            flex: 1;
            overflow-y: auto;
            padding: 20px 28px;
            display: flex;
            flex-direction: column;
            gap: 14px;
        }
        .terms-clause {
            display: flex;
            align-items: flex-start;
            gap: 14px;
            padding: 16px 18px;
            border-radius: 18px;
            border: 1.5px solid rgba(7, 45, 48, 0.09);
            background: #ffffff;
            box-shadow: 0 8px 20px -18px rgba(7, 45, 48, 0.35);
            transition: border-color 0.25s ease, box-shadow 0.25s ease, background 0.25s ease;
        }
        .terms-clause.is-read {
            border-color: rgba(16, 162, 148, 0.35);
            background: linear-gradient(145deg, #ffffff 0%, #f7fdfb 100%);
        }
        .terms-clause.is-unread-alert {
            border-color: rgba(217, 119, 6, 0.55);
            background: #fffdf7;
        }
        .terms-clause__icon {
            display: grid;
            place-items: center;
            width: 40px;
            height: 40px;
            flex-shrink: 0;
            border-radius: 12px;
            background: #e6f9f3;
            color: #0a7a70;
            font-size: 1rem;
        }
        .terms-clause.is-read .terms-clause__icon {
            background: linear-gradient(135deg, #14b8a6, #0a7a70);
            color: #ffffff;
        }
        .terms-clause__content {
            flex: 1;
            min-width: 0;
        }
        .terms-clause h4 {
            margin: 0 0 5px;
            font-family: "Sora", sans-serif;
            font-size: 0.91rem;
            font-weight: 700;
            color: #072d30;
        }
        .terms-clause__summary {
            margin: 0;
            font-size: 0.81rem;
            line-height: 1.56;
            color: #4f6d6f;
        }
        .terms-read-more-btn {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            margin-top: 6px;
            padding: 0;
            border: 0;
            background: transparent;
            color: #0a7a70;
            font-size: 0.78rem;
            font-weight: 800;
            cursor: pointer;
            text-decoration: underline;
            text-underline-offset: 3px;
            transition: color 0.2s ease;
        }
        .terms-read-more-btn:hover {
            color: #054d46;
        }
        .terms-clause.is-unread-alert .terms-read-more-btn {
            color: #b45309;
        }
        .terms-clause__full {
            margin-top: 10px;
            padding: 13px 15px;
            border-radius: 12px;
            border-left: 3px solid #10a294;
            background: #f4fbf9;
            font-size: 0.79rem;
            line-height: 1.58;
            color: #244749;
        }
        .terms-clause__full[hidden] {
            display: none;
        }
        .terms-clause__full p {
            margin: 0 0 8px;
        }
        .terms-clause__full ul {
            margin: 6px 0 8px;
            padding-left: 18px;
        }
        .terms-clause__full li + li {
            margin-top: 4px;
        }
        .terms-clause__legal-ref {
            display: inline-block;
            margin-top: 4px;
            padding: 3px 9px;
            border-radius: 6px;
            background: rgba(16, 162, 148, 0.12);
            color: #065f5b;
            font-size: 0.72rem;
            font-weight: 800;
        }

        /* Caja de consentimiento final */
        .terms-consent-box {
            padding: 15px 18px;
            border-radius: 16px;
            border: 1.5px solid rgba(16, 162, 148, 0.35);
            background: linear-gradient(135deg, #e8fbf5 0%, #f4fdf9 100%);
        }
        .terms-lock-banner {
            display: flex;
            align-items: flex-start;
            gap: 9px;
            margin-bottom: 12px;
            padding: 10px 13px;
            border-radius: 11px;
            background: #fffbeb;
            border: 1px solid rgba(217, 119, 6, 0.38);
            color: #92400e;
            font-size: 0.78rem;
            font-weight: 700;
            line-height: 1.45;
            animation: termsAlertShake 0.38s ease;
        }
        .terms-lock-banner[hidden] {
            display: none !important;
        }
        .terms-lock-banner i {
            margin-top: 2px;
            font-size: 0.92rem;
            color: #d97706;
        }
        @keyframes termsAlertShake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            50% { transform: translateX(5px); }
            75% { transform: translateX(-3px); }
        }
        .terms-consent-label {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            cursor: pointer;
            margin: 0;
            font-size: 0.82rem;
            font-weight: 700;
            color: #073b38;
            line-height: 1.5;
        }
        .terms-consent-label input[type="checkbox"] {
            width: 20px;
            height: 20px;
            margin-top: 2px;
            accent-color: #0a7a70;
            cursor: pointer;
            flex-shrink: 0;
        }
        .terms-modal__footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 15px 28px;
            border-top: 1px solid rgba(7, 45, 48, 0.1);
            background: #ffffff;
        }
        .btn-confirm-terms:disabled {
            opacity: 0.52;
            cursor: not-allowed;
            transform: none !important;
        }
        @media (max-width: 640px) {
            .terms-modal__header,
            .terms-applicant-bar,
            .terms-modal__body,
            .terms-modal__footer {
                padding-inline: 18px;
            }
            .terms-modal__footer {
                flex-direction: column-reverse;
                align-items: stretch;
            }
            .terms-modal__footer .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body class="admin-body patient-auth-body">

    <main class="patient-auth-card patient-register-card">
        <div class="d-flex justify-content-between align-items-start mb-4 pb-3 border-bottom">
            <div>
                <span class="eyebrow mb-2" style="display:inline-flex;align-items:center;gap:6px;padding:5px 12px;border-radius:999px;background:rgba(16,162,148,0.12);color:#087a70;font-size:0.7rem;font-weight:800;letter-spacing:0.11em;text-transform:uppercase;">
                    <i class="fa-solid fa-tooth"></i> <?php echo $modoAdmin ? 'Recepción DENTIX' : 'Portal del paciente'; ?>
                </span>
                <h1 class="fw-bold mb-1" style="font-size: 1.75rem; color: #072d30;">
                    <i class="fa-solid fa-user-plus me-2" style="color: #0a8f82;"></i><?php echo $modoAdmin ? 'Registrar paciente' : 'Crear mi cuenta'; ?>
                </h1>
                <p class="mb-0 small" style="color: #5b787a;">Completa tus datos personales y clínicos para brindarte una atención segura.</p>
            </div>
            <a href="<?php echo $modoAdmin ? 'lista_pacientes.php' : '../../index.php'; ?>" class="patient-auth-back mt-0">
                <i class="fa-solid fa-arrow-left"></i> Volver
            </a>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger py-2 mb-4 small d-flex align-items-center rounded-3" role="alert">
                <i class="fa-solid fa-triangle-exclamation me-2 fs-5"></i>
                <div><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
            </div>
        <?php endif; ?>

        <!-- Alerta dinámica de validación en cliente -->
        <div id="clientValidationAlert" class="alert alert-danger py-2 mb-4 small d-none align-items-center rounded-3" role="alert">
            <i class="fa-solid fa-triangle-exclamation me-2 fs-5"></i>
            <div id="clientValidationText"></div>
        </div>

        <form id="patientRegisterForm" action="form_paciente.php<?php echo $modoAdmin ? '?desde=admin' : ''; ?>" method="POST" novalidate>
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
            <input type="hidden" name="acepto_terminos" id="acepto_terminos_input" value="0">
            <?php if ($modoAdmin): ?>
                <input type="hidden" name="desde" value="admin">
            <?php endif; ?>

            <span class="register-section-tag"><i class="fa-solid fa-id-badge"></i> Datos personales y de contacto</span>

            <div class="mb-3 register-field-group">
                <label for="nombre" class="form-label fw-bold small mb-1">Nombres y Apellidos</label>
                <div class="input-group">
                    <span class="input-group-text border-end-0"><i class="fa-solid fa-user"></i></span>
                    <input type="text" class="form-control border-start-0" id="nombre" name="nombre" required maxlength="60" autocomplete="name" placeholder="Ej. Juan Pérez" value="<?php echo htmlspecialchars($nombreVal, ENT_QUOTES, 'UTF-8'); ?>">
                </div>
                <small class="text-muted" style="font-size: 0.74rem;">Cada palabra debe iniciar con mayúscula seguida de minúsculas (Ej. <strong>Juan Steven</strong>).</small>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-sm-6 register-field-group">
                    <label for="cedula" class="form-label fw-bold small mb-1">Número de Cédula</label>
                    <div class="input-group">
                        <span class="input-group-text border-end-0"><i class="fa-solid fa-id-card"></i></span>
                        <input type="text" class="form-control border-start-0" id="cedula" name="cedula" required maxlength="20" autocomplete="username" inputmode="numeric" placeholder="Ej. 1001234567" value="<?php echo htmlspecialchars($cedulaVal, ENT_QUOTES, 'UTF-8'); ?>">
                    </div>
                </div>

                <div class="col-sm-6 register-field-group">
                    <label for="fecha_nac" class="form-label fw-bold small mb-1">Fecha de Nacimiento</label>
                    <div class="input-group">
                        <span class="input-group-text border-end-0"><i class="fa-solid fa-cake-candles"></i></span>
                        <input type="date" class="form-control border-start-0" id="fecha_nac" name="fecha_nac" required min="1900-01-01" max="<?php echo date('Y-m-d'); ?>" value="<?php echo htmlspecialchars($fechaNacVal, ENT_QUOTES, 'UTF-8'); ?>">
                    </div>
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-sm-6 register-field-group">
                    <label for="telefono" class="form-label fw-bold small mb-1">Teléfono / Celular</label>
                    <div class="input-group">
                        <span class="input-group-text border-end-0"><i class="fa-solid fa-phone"></i></span>
                        <input type="text" class="form-control border-start-0" id="telefono" name="telefono" required maxlength="15" autocomplete="tel" placeholder="Ej. 3001234567" value="<?php echo htmlspecialchars($telefonoVal, ENT_QUOTES, 'UTF-8'); ?>">
                    </div>
                </div>

                <div class="col-sm-6 register-field-group">
                    <label for="correo" class="form-label fw-bold small mb-1">Correo Electrónico</label>
                    <div class="input-group">
                        <span class="input-group-text border-end-0"><i class="fa-solid fa-envelope"></i></span>
                        <input type="email" class="form-control border-start-0" id="correo" name="correo" required maxlength="80" autocomplete="email" placeholder="correo@ejemplo.com" value="<?php echo htmlspecialchars($correoVal, ENT_QUOTES, 'UTF-8'); ?>">
                    </div>
                </div>
            </div>

            <span class="register-section-tag mt-2"><i class="fa-solid fa-notes-medical"></i> Información clínica importante</span>

            <div class="allergy-box mb-3 register-field-group">
                <label for="alergia_select" class="form-label fw-bold small mb-1">
                    Alergias o Alertas Médicas
                </label>
                <div class="input-group mb-2">
                    <span class="input-group-text border-end-0"><i class="fa-solid fa-hand-dots"></i></span>
                    <select class="form-select border-start-0" id="alergia_select" name="alergia_select">
                        <?php foreach ($catalogoAlergias as $valor => $etiqueta): ?>
                            <option value="<?php echo htmlspecialchars($valor, ENT_QUOTES, 'UTF-8'); ?>" <?php echo ($alergiaSelectVal === $valor) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($etiqueta, ENT_QUOTES, 'UTF-8'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div id="alergiaManualWrap" class="mt-2">
                    <div class="input-group">
                        <span class="input-group-text border-end-0"><i class="fa-solid fa-pen-to-square"></i></span>
                        <input
                            type="text"
                            class="form-control border-start-0"
                            id="alergia_manual"
                            name="alergia_manual"
                            maxlength="90"
                            placeholder="¿Tienes otra alergia o deseas especificar? Escríbela aquí (opcional)..."
                            value="<?php echo htmlspecialchars($alergiaManualVal, ENT_QUOTES, 'UTF-8'); ?>"
                        >
                    </div>
                </div>

                <div class="allergy-chips" aria-label="Selección rápida de alergias comunes">
                    <button type="button" class="allergy-chip" data-allergy-value="Ninguna registrada">Ninguna</button>
                    <button type="button" class="allergy-chip" data-allergy-value="Penicilina / Amoxicilina (Antibióticos)">Penicilina</button>
                    <button type="button" class="allergy-chip" data-allergy-value="Anestesia local (Lidocaína / Articaína)">Anestesia local</button>
                    <button type="button" class="allergy-chip" data-allergy-value="Ibuprofeno / AINEs / Aspirina (Analgésicos)">Ibuprofeno / AINEs</button>
                    <button type="button" class="allergy-chip" data-allergy-value="Látex (Guantes o insumos)">Látex</button>
                    <button type="button" class="allergy-chip" data-allergy-value="Otra">+ Escribir otra</button>
                </div>
            </div>

            <span class="register-section-tag mt-1"><i class="fa-solid fa-shield-halved"></i> Seguridad de la cuenta</span>

            <div class="row g-3 mb-4">
                <div class="col-sm-6 register-field-group">
                    <label for="password" class="form-label fw-bold small mb-1">Crea una contraseña</label>
                    <div class="input-group">
                        <span class="input-group-text border-end-0"><i class="fa-solid fa-lock"></i></span>
                        <input type="password" class="form-control border-start-0" id="password" name="password" required minlength="8" maxlength="72" autocomplete="new-password" placeholder="Mínimo 8 caracteres">
                    </div>
                </div>

                <div class="col-sm-6 register-field-group">
                    <label for="password_confirmacion" class="form-label fw-bold small mb-1">Confirma tu contraseña</label>
                    <div class="input-group">
                        <span class="input-group-text border-end-0"><i class="fa-solid fa-shield-halved"></i></span>
                        <input type="password" class="form-control border-start-0" id="password_confirmacion" name="password_confirmacion" required minlength="8" maxlength="72" autocomplete="new-password" placeholder="Repite tu contraseña">
                    </div>
                </div>
            </div>

            <div class="d-grid gap-2">
                <button type="submit" class="btn btn--primary py-2 fw-bold">
                    <i class="fa-solid fa-user-check me-2"></i> <?php echo $modoAdmin ? 'Guardar paciente' : 'Crear cuenta'; ?>
                </button>
            </div>
        </form>

        <?php if (!$modoAdmin): ?>
            <div class="patient-auth-footer">
                <span>¿Ya tienes una cuenta?</span>
                <a href="login_paciente.php">Inicia sesión</a>
            </div>
        <?php endif; ?>
    </main>

    <!-- =========================================================
         VENTANA MODAL PROFESIONAL: TÉRMINOS Y CONDICIONES CLÍNICOS
         ========================================================= -->
    <div class="terms-modal" id="termsModal" role="dialog" aria-modal="true" aria-labelledby="termsModalTitle" aria-hidden="true">
        <div class="terms-modal__backdrop" data-close-terms></div>
        <div class="terms-modal__dialog" role="document">
            <header class="terms-modal__header">
                <span class="terms-modal__badge">
                    <i class="fa-solid fa-scale-balanced"></i> Protección de Datos y Ética Clínica · Ley 1581 de 2012
                </span>
                <h2 id="termsModalTitle">Términos, Condiciones y Tratamiento de Datos de Salud</h2>
                <p>Tus datos han sido verificados. Antes de finalizar la creación de tu cuenta en DENTIX, revisa y acepta el consentimiento de uso clínico y protección de tu información personal.</p>
            </header>

            <div class="terms-applicant-bar">
                <span><i class="fa-solid fa-user-check me-1"></i> Titular validado: <strong id="termsApplicantName">—</strong></span>
                <span><i class="fa-solid fa-id-card me-1"></i> Documento: <strong id="termsApplicantId">—</strong></span>
            </div>

            <div class="terms-modal__body">
                <!-- CLÁUSULA 1 -->
                <article class="terms-clause" data-clause-card="1">
                    <div class="terms-clause__icon"><i class="fa-solid fa-user-shield"></i></div>
                    <div class="terms-clause__content">
                        <h4>1. Autorización de Tratamiento de Datos Personales (Habeas Data)</h4>
                        <p class="terms-clause__summary">
                            En cumplimiento de la <strong>Ley Estatutaria 1581 de 2012</strong> y el <strong>Decreto 1377 de 2013</strong>, autorizas a <strong>DENTIX Clínica Odontológica</strong> para recolectar, almacenar y gestionar tus datos de identificación y contacto con fines asistenciales y administrativos.
                        </p>
                        <button type="button" class="terms-read-more-btn" data-clause-toggle="1" aria-expanded="false">
                            <span>Ver más...</span> <i class="fa-solid fa-chevron-down" style="font-size:0.68rem;"></i>
                        </button>
                        <div class="terms-clause__full" id="clauseFull1" hidden>
                            <p><strong>Alcance Constitucional y Legal:</strong> De conformidad con el Artículo 15 de la Constitución Política de Colombia, la Ley 1581 de 2012 y el Decreto Único Reglamentario 1074 de 2015, el titular otorga su consentimiento previo, expreso e informado para que sus datos personales (nombres y apellidos, número de cédula de ciudadanía, fecha de nacimiento, teléfono móvil y dirección de correo electrónico) sean incorporados en la base de datos institucional de <strong>DENTIX</strong>.</p>
                            <p><strong>Finalidades Específicas Autorizadas:</strong></p>
                            <ul>
                                <li>Creación, autenticación y administración de tu cuenta personal dentro del Portal del Paciente DENTIX.</li>
                                <li>Programación, confirmación, reprogramación o cancelación de citas odontológicas por parte del área de Recepción.</li>
                                <li>Contacto telefónico o por correo electrónico para recordatorios de agenda, preparación previa a procedimientos y seguimiento postoperatorio.</li>
                                <li>Generación de reportes estadísticos internos anonimizados para mejorar la calidad y oportunidad del servicio odontológico.</li>
                            </ul>
                            <span class="terms-clause__legal-ref">Soporte Normativo: Art. 15 Constitución Política · Ley 1581 de 2012 · Decreto 1377 de 2013</span>
                        </div>
                    </div>
                </article>

                <!-- CLÁUSULA 2 -->
                <article class="terms-clause" data-clause-card="2">
                    <div class="terms-clause__icon"><i class="fa-solid fa-heart-pulse"></i></div>
                    <div class="terms-clause__content">
                        <h4>2. Uso Profesional de Información Clínica y Alergias Médicas</h4>
                        <p class="terms-clause__summary">
                            Los datos sensibles de salud (alergias a medicamentos o insumos, motivos de consulta, diagnósticos y procedimientos) serán utilizados <strong>exclusivamente por el cuerpo odontológico autorizado</strong> para tu seguridad clínica.
                        </p>
                        <button type="button" class="terms-read-more-btn" data-clause-toggle="2" aria-expanded="false">
                            <span>Ver más...</span> <i class="fa-solid fa-chevron-down" style="font-size:0.68rem;"></i>
                        </button>
                        <div class="terms-clause__full" id="clauseFull2" hidden>
                            <p><strong>Tratamiento de Datos Sensibles de Salud (Art. 6 Ley 1581 de 2012):</strong> Se informa al paciente que los datos relativos a su estado de salud oral, alergias medicamentosas (penicilina, amoxicilina, anestésicos locales como lidocaína/articaína, analgésicos AINEs, látex, yodo/clorhexidina o metales) constituyen datos sensibles recolectados con la única finalidad de salvaguardar su vida e integridad física durante los tratamientos odontológicos.</p>
                            <p><strong>Protocolo de Seguridad Clínica y Farmacovigilancia:</strong></p>
                            <ul>
                                <li>Tu reporte de alergias estará visible de manera prioritaria para el odontólogo tratante antes de aplicar cualquier anestésico, biomaterial o prescripción farmacológica.</li>
                                <li>El registro de cierre clínico (<code>detalle_cita</code>: diagnóstico definitivo, descripción del procedimiento ejecutado y notas clínicas de evolución) es diligenciado exclusivamente por profesionales en Odontología con cuenta activa en el sistema.</li>
                                <li>El paciente declara que la información de alergias y antecedentes suministrada es veraz y se compromete a actualizarla desde su perfil ante cualquier cambio médico.</li>
                            </ul>
                            <span class="terms-clause__legal-ref">Soporte Normativo: Art. 6 Ley 1581 de 2012 · Resolución 3100 de 2019 (Seguridad del Paciente)</span>
                        </div>
                    </div>
                </article>

                <!-- CLÁUSULA 3 -->
                <article class="terms-clause" data-clause-card="3">
                    <div class="terms-clause__icon"><i class="fa-solid fa-file-medical"></i></div>
                    <div class="terms-clause__content">
                        <h4>3. Reserva Legal de la Historia Clínica Odontológica</h4>
                        <p class="terms-clause__summary">
                            Conforme a la <strong>Ley 35 de 1989</strong> (Código de Ética del Odontólogo Colombiano) y la <strong>Resolución 1995 de 1999</strong> del Ministerio de Salud, tu historial clínico está sometido a estricta reserva legal.
                        </p>
                        <button type="button" class="terms-read-more-btn" data-clause-toggle="3" aria-expanded="false">
                            <span>Ver más...</span> <i class="fa-solid fa-chevron-down" style="font-size:0.68rem;"></i>
                        </button>
                        <div class="terms-clause__full" id="clauseFull3" hidden>
                            <p><strong>Carácter Privado y Secreto Profesional:</strong> En virtud de la Ley 35 de 1989, la Ley 23 de 1981 y la Resolución 1995 de 1999 expedida por el Ministerio de Salud, la historia clínica odontológica es un documento privado, obligatorio y sometido a reserva, en el cual se registran cronológicamente las condiciones de salud oral del paciente, los actos médicos y los procedimientos ejecutados.</p>
                            <p><strong>Garantías de Confidencialidad en DENTIX:</strong></p>
                            <ul>
                                <li>Ningún dato clínico, diagnóstico o procedimiento será comercializado, cedido ni divulgado a terceros no autorizados.</li>
                                <li>Únicamente tienen acceso al expediente clínico el paciente titular, el equipo odontológico y asistencial involucrado en su atención o las autoridades judiciales y sanitarias en los casos estrictamente previstos por la ley.</li>
                                <li>El motor de base de datos de DENTIX aplica restricciones de integridad referencial que impiden la alteración o eliminación no autorizada de historias clínicas con atenciones registradas.</li>
                            </ul>
                            <span class="terms-clause__legal-ref">Soporte Normativo: Ley 35 de 1989 · Ley 23 de 1981 · Resolución 1995 de 1999 MinSalud</span>
                        </div>
                    </div>
                </article>

                <!-- CLÁUSULA 4 -->
                <article class="terms-clause" data-clause-card="4">
                    <div class="terms-clause__icon"><i class="fa-solid fa-calendar-check"></i></div>
                    <div class="terms-clause__content">
                        <h4>4. Políticas de Agendamiento, Puntualidad y Cancelación</h4>
                        <p class="terms-clause__summary">
                            El paciente se compromete a cumplir el reglamento de agenda clínica (horario de <strong>08:00 a. m. a 07:30 p. m.</strong> en bloques de 30 minutos), respetando las reglas de disponibilidad y aviso oportuno.
                        </p>
                        <button type="button" class="terms-read-more-btn" data-clause-toggle="4" aria-expanded="false">
                            <span>Ver más...</span> <i class="fa-solid fa-chevron-down" style="font-size:0.68rem;"></i>
                        </button>
                        <div class="terms-clause__full" id="clauseFull4" hidden>
                            <p><strong>Reglas Operativas del Sistema de Citas (RN-01 a RN-08):</strong> Para garantizar una atención puntual, ordenada y equitativa para toda la comunidad de pacientes, la plataforma aplica las siguientes políticas de agendamiento:</p>
                            <ul>
                                <li><strong>Horario y Bloques de Atención:</strong> Las citas se programan exclusivamente entre las <code>08:00</code> y las <code>19:30</code> horas en intervalos exactos de 30 minutos (<code>:00</code> y <code>:30</code>).</li>
                                <li><strong>Proceso de Confirmación:</strong> Toda solicitud realizada en línea ingresa en estado <strong>Pendiente</strong>. El área de Recepción verificará la agenda, asignará el odontólogo disponible y cambiará el estado a <strong>Confirmada</strong>.</li>
                                <li><strong>Límite Diario e Intervalo Mínimo:</strong> Por seguridad clínica y control de agenda, un paciente solo puede mantener <strong>una (1) cita activa por día</strong> y debe existir un espaciado mínimo de <strong>cuatro (4) días completos</strong> entre citas activas del mismo titular.</li>
                                <li><strong>Asistencia y Cancelación:</strong> El paciente deberá presentarse 15 minutos antes de la hora confirmada con su documento de identidad. En caso de no poder asistir, deberá solicitar la cancelación o reprogramación indicando el motivo correspondiente.</li>
                            </ul>
                            <span class="terms-clause__legal-ref">Soporte Normativo: Reglamento Interno de Agenda Clínica DENTIX · Reglas RN-01 a RN-08</span>
                        </div>
                    </div>
                </article>

                <!-- CLÁUSULA 5 -->
                <article class="terms-clause" data-clause-card="5">
                    <div class="terms-clause__icon"><i class="fa-solid fa-lock"></i></div>
                    <div class="terms-clause__content">
                        <h4>5. Derechos del Titular (ARCO) y Seguridad de la Cuenta</h4>
                        <p class="terms-clause__summary">
                            Como titular tienes derecho a conocer, actualizar y rectificar tus datos personales en cualquier momento. Tu contraseña se resguarda bajo cifrado criptográfico seguro (<code>bcrypt</code>).
                        </p>
                        <button type="button" class="terms-read-more-btn" data-clause-toggle="5" aria-expanded="false">
                            <span>Ver más...</span> <i class="fa-solid fa-chevron-down" style="font-size:0.68rem;"></i>
                        </button>
                        <div class="terms-clause__full" id="clauseFull5" hidden>
                            <p><strong>Ejercicio de Derechos ARCO (Art. 8 Ley 1581 de 2012):</strong> El titular de los datos personales cuenta en todo momento con los siguientes derechos garantizados por la plataforma:</p>
                            <ul>
                                <li><strong>Acceso y Consulta:</strong> Consultar de forma gratuita sus datos personales, alergias registradas y el estado o historial de sus citas desde su Portal del Paciente.</li>
                                <li><strong>Actualización y Rectificación:</strong> Modificar sus datos de contacto, correo electrónico, alergias o contraseña, o solicitar la corrección de información inexacta ante Recepción.</li>
                                <li><strong>Revocatoria y Supresión:</strong> Solicitar la supresión de su cuenta cuando no exista un deber legal o clínico de conservación de historia médica vigente.</li>
                                <li><strong>Ciberseguridad y Custodia de Credenciales:</strong> Las contraseñas se almacenan mediante funciones de hash irreversibles (<code>bcrypt</code>), las sesiones están protegidas con cookies <code>HttpOnly</code> y tokens <code>CSRF</code>. El paciente es responsable de no compartir su clave personal con terceros.</li>
                            </ul>
                            <span class="terms-clause__legal-ref">Soporte Normativo: Art. 8 Ley 1581 de 2012 · Estándares de Seguridad OWASP / PDO</span>
                        </div>
                    </div>
                </article>

                <!-- CAJA DE CONSENTIMIENTO (EL AVISO SALE DE SORPRESA SI NO HA LEÍDO LOS 5 PUNTOS) -->
                <div class="terms-consent-box">
                    <div class="terms-lock-banner" id="termsLockBanner" hidden>
                        <i class="fa-solid fa-triangle-exclamation"></i>
                        <span id="termsLockBannerText"></span>
                    </div>
                    <label class="terms-consent-label" id="termsConsentLabel" for="termsConsentCheck">
                        <input type="checkbox" id="termsConsentCheck">
                        <span>He leído íntegramente los 5 puntos desplegados y acepto los <strong>Términos y Condiciones de Uso</strong>, autorizando de manera previa, libre y expresa el <strong>Tratamiento de mis Datos Personales y Clínicos</strong> para fines profesionales de salud oral en DENTIX.</span>
                    </label>
                </div>
            </div>

            <footer class="terms-modal__footer">
                <button type="button" class="btn btn--ghost btn--small" data-close-terms>
                    <i class="fa-solid fa-arrow-left me-1"></i> Revisar mis datos
                </button>
                <button type="button" class="btn btn--primary btn--small btn-confirm-terms" id="confirmTermsBtn">
                    <i class="fa-solid fa-shield-heart me-1"></i> Aceptar términos y crear cuenta
                </button>
            </footer>
        </div>
    </div>

    <script>
        (() => {
            const form = document.getElementById('patientRegisterForm');
            const select = document.getElementById('alergia_select');
            const manualInput = document.getElementById('alergia_manual');
            const chips = document.querySelectorAll('[data-allergy-value]');
            const alertBox = document.getElementById('clientValidationAlert');
            const alertText = document.getElementById('clientValidationText');

            const termsModal = document.getElementById('termsModal');
            const closeTermsBtns = document.querySelectorAll('[data-close-terms]');
            const termsCheck = document.getElementById('termsConsentCheck');
            const termsLockBanner = document.getElementById('termsLockBanner');
            const termsLockBannerText = document.getElementById('termsLockBannerText');
            const confirmTermsBtn = document.getElementById('confirmTermsBtn');
            const aceptoTerminosInput = document.getElementById('acepto_terminos_input');
            const applicantNameEl = document.getElementById('termsApplicantName');
            const applicantIdEl = document.getElementById('termsApplicantId');
            const clauseToggleBtns = document.querySelectorAll('[data-clause-toggle]');

            const TOTAL_CLAUSES = 5;
            const readClauses = new Set();

            if (!form || !select || !manualInput) return;

            const showClientError = (msg, field) => {
                if (alertBox && alertText) {
                    alertText.textContent = msg;
                    alertBox.classList.remove('d-none');
                    alertBox.classList.add('d-flex');
                    alertBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
                if (field && typeof field.focus === 'function') {
                    field.focus();
                }
            };

            const hideClientError = () => {
                if (alertBox) {
                    alertBox.classList.add('d-none');
                    alertBox.classList.remove('d-flex');
                }
            };

            const syncChips = () => {
                chips.forEach((chip) => {
                    chip.classList.toggle('is-active', chip.dataset.allergyValue === select.value);
                });

                if (select.value === 'Otra') {
                    manualInput.required = true;
                    manualInput.placeholder = 'Escribe aquí a qué medicamento o sustancia eres alérgico (obligatorio)...';
                } else {
                    manualInput.required = false;
                    manualInput.placeholder = '¿Deseas agregar algún detalle adicional sobre tu alergia? Escríbelo aquí (opcional)...';
                }
            };

            select.addEventListener('change', syncChips);

            chips.forEach((chip) => {
                chip.addEventListener('click', () => {
                    select.value = chip.dataset.allergyValue;
                    syncChips();
                    if (select.value === 'Otra') {
                        manualInput.focus();
                    }
                });
            });

            syncChips();

            const showSurpriseReadingWarning = () => {
                const pendingIds = [];
                for (let i = 1; i <= TOTAL_CLAUSES; i++) {
                    const card = document.querySelector('[data-clause-card="' + i + '"]');
                    if (!readClauses.has(String(i))) {
                        pendingIds.push(i);
                        if (card) card.classList.add('is-unread-alert');
                    } else if (card) {
                        card.classList.remove('is-unread-alert');
                    }
                }

                if (termsLockBanner && termsLockBannerText) {
                    termsLockBanner.hidden = false;
                    termsLockBanner.style.animation = 'none';
                    void termsLockBanner.offsetWidth;
                    termsLockBanner.style.animation = '';

                    if (pendingIds.length > 0) {
                        termsLockBannerText.innerHTML =
                            '¡Atención! Para poder confirmar que leíste todo, primero debes hacer clic en <strong>"Ver más..."</strong> y revisar la información completa de los 5 puntos (te falta leer: <strong>' +
                            pendingIds.join(', ') + '</strong>).';
                    } else {
                        termsLockBannerText.innerHTML =
                            'Por favor marca la casilla de aceptación para confirmar los términos y condiciones.';
                    }
                    termsLockBanner.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                }
            };

            const hideSurpriseWarningIfReady = () => {
                if (readClauses.size >= TOTAL_CLAUSES && termsLockBanner) {
                    termsLockBanner.hidden = true;
                }
            };

            clauseToggleBtns.forEach((btn) => {
                btn.addEventListener('click', () => {
                    const clauseId = btn.dataset.clauseToggle;
                    const fullBox = document.getElementById('clauseFull' + clauseId);
                    const card = document.querySelector('[data-clause-card="' + clauseId + '"]');

                    if (!fullBox) return;

                    const isCurrentlyHidden = fullBox.hasAttribute('hidden');
                    if (isCurrentlyHidden) {
                        fullBox.removeAttribute('hidden');
                        btn.setAttribute('aria-expanded', 'true');
                        btn.innerHTML = '<span>Ver menos</span> <i class="fa-solid fa-chevron-up" style="font-size:0.68rem;"></i>';

                        readClauses.add(clauseId);
                        if (card) {
                            card.classList.add('is-read');
                            card.classList.remove('is-unread-alert');
                        }
                        hideSurpriseWarningIfReady();
                    } else {
                        fullBox.setAttribute('hidden', '');
                        btn.setAttribute('aria-expanded', 'false');
                        btn.innerHTML = '<span>Ver más...</span> <i class="fa-solid fa-chevron-down" style="font-size:0.68rem;"></i>';
                    }
                });
            });

            const openTermsModal = (nombre, cedula) => {
                if (applicantNameEl) applicantNameEl.textContent = nombre;
                if (applicantIdEl) applicantIdEl.textContent = 'CC ' + cedula;

                if (termsLockBanner) termsLockBanner.hidden = true;
                document.querySelectorAll('.terms-clause.is-unread-alert').forEach((el) => {
                    el.classList.remove('is-unread-alert');
                });

                termsModal.classList.add('is-open');
                termsModal.setAttribute('aria-hidden', 'false');
                document.body.classList.add('is-terms-open');
            };

            const closeTermsModal = () => {
                termsModal.classList.remove('is-open');
                termsModal.setAttribute('aria-hidden', 'true');
                document.body.classList.remove('is-terms-open');
            };

            closeTermsBtns.forEach((btn) => {
                btn.addEventListener('click', closeTermsModal);
            });

            if (termsCheck && confirmTermsBtn) {
                termsCheck.addEventListener('click', (e) => {
                    if (readClauses.size < TOTAL_CLAUSES) {
                        e.preventDefault();
                        termsCheck.checked = false;
                        showSurpriseReadingWarning();
                    }
                });

                termsCheck.addEventListener('change', () => {
                    if (readClauses.size < TOTAL_CLAUSES) {
                        termsCheck.checked = false;
                        showSurpriseReadingWarning();
                        return;
                    }
                    if (termsLockBanner && termsCheck.checked) {
                        termsLockBanner.hidden = true;
                    }
                });

                confirmTermsBtn.addEventListener('click', () => {
                    if (readClauses.size < TOTAL_CLAUSES) {
                        termsCheck.checked = false;
                        showSurpriseReadingWarning();
                        return;
                    }
                    if (!termsCheck.checked) {
                        showSurpriseReadingWarning();
                        termsCheck.focus();
                        return;
                    }
                    aceptoTerminosInput.value = '1';
                    confirmTermsBtn.disabled = true;
                    confirmTermsBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i> Creando cuenta...';
                    form.submit();
                });
            }

            window.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && termsModal.classList.contains('is-open')) {
                    closeTermsModal();
                }
            });

            form.addEventListener('submit', (e) => {
                if (aceptoTerminosInput.value === '1') {
                    return;
                }

                e.preventDefault();
                hideClientError();

                const nombreInput = document.getElementById('nombre');
                const cedulaInput = document.getElementById('cedula');
                const fechaNacInput = document.getElementById('fecha_nac');
                const telefonoInput = document.getElementById('telefono');
                const correoInput = document.getElementById('correo');
                const passInput = document.getElementById('password');
                const passConfInput = document.getElementById('password_confirmacion');

                const nombre = (nombreInput.value || '').trim();
                const cedula = (cedulaInput.value || '').trim();
                const fechaNac = (fechaNacInput.value || '').trim();
                const telefono = (telefonoInput.value || '').trim();
                const correo = (correoInput.value || '').trim();
                const pass = passInput.value || '';
                const passConf = passConfInput.value || '';

                const regexNombreTitulo = /^[A-ZÁÉÍÓÚÜÑ][a-záéíóúüñ]+(?: [A-ZÁÉÍÓÚÜÑ][a-záéíóúüñ]+)*$/u;
                const regexEmail = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

                if (!nombre) {
                    showClientError('Por favor ingresa tus nombres y apellidos.', nombreInput);
                    return;
                }
                if (!regexNombreTitulo.test(nombre)) {
                    showClientError('Cada palabra del nombre debe empezar con mayúscula seguida de minúsculas (ejemplo: Juan Steven). No se permite todo en mayúsculas ni en minúsculas.', nombreInput);
                    return;
                }
                if (!cedula) {
                    showClientError('Por favor ingresa tu número de cédula.', cedulaInput);
                    return;
                }
                if (!fechaNac) {
                    showClientError('Por favor selecciona tu fecha de nacimiento.', fechaNacInput);
                    return;
                }
                if (!telefono) {
                    showClientError('Por favor ingresa tu número de teléfono o celular.', telefonoInput);
                    return;
                }
                if (!correo || !regexEmail.test(correo)) {
                    showClientError('Por favor ingresa un correo electrónico válido.', correoInput);
                    return;
                }
                if (select.value === 'Otra' && !(manualInput.value || '').trim()) {
                    showClientError('Por favor escribe cuál es tu alergia en el campo de texto.', manualInput);
                    return;
                }
                if (pass.length < 8) {
                    showClientError('La contraseña debe tener al menos 8 caracteres.', passInput);
                    return;
                }
                if (pass !== passConf) {
                    showClientError('Las contraseñas no coinciden. Verifícalas por favor.', passConfInput);
                    return;
                }

                // Todos los datos están validados: abrir la ventana profesional de Términos y Condiciones
                openTermsModal(nombre, cedula);
            });
        })();
    </script>
    <script src="../../assests/js/dentix-i18n.js?v=20261110-cur"></script>
</body>
</html>
