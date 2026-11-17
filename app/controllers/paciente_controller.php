<?php

declare(strict_types=1);

require_once APP_PATH . '/models/paciente_model.php';
require_once APP_PATH . '/models/cita_model.php';
require_once APP_PATH . '/models/servicio_model.php';

function paciente_catalogo_alergias(): array
{
    return [
        'Ninguna registrada' => 'Ninguna alergia conocida',
        'Penicilina / Amoxicilina (Antibióticos)' => 'Penicilina / Amoxicilina (Antibióticos)',
        'Anestesia local (Lidocaína / Articaína)' => 'Anestesia local (Lidocaína / Articaína)',
        'Ibuprofeno / AINEs / Aspirina (Analgésicos)' => 'Ibuprofeno / AINEs / Aspirina (Analgésicos)',
        'Látex (Guantes o insumos)' => 'Látex (Guantes o insumos)',
        'Yodo / Clorhexidina (Antisépticos)' => 'Yodo / Clorhexidina (Antisépticos)',
        'Metales dentales (Níquel / Cromo / Cobalto)' => 'Metales dentales (Níquel / Cromo / Cobalto)',
        'Otra' => 'Otra alergia (Escribir manualmente...)',
    ];
}

function paciente_login(): void
{
    iniciar_sesion();

    if (paciente_autenticado()) {
        redirect('perfil-paciente');
    }

    $nextSolicitado = $_GET['next'] ?? $_POST['next'] ?? '';
    $next = in_array($nextSolicitado, ['portal-paciente', 'portal_paciente.php'], true)
        ? 'portal-paciente'
        : 'perfil-paciente';

    $error = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $cedula = is_string($_POST['cedula'] ?? null) ? trim($_POST['cedula']) : '';
        $password = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';

        if (!validar_csrf_paciente()) {
            $error = 'La sesión del formulario venció. Recarga la página e inténtalo de nuevo.';
        } elseif ($cedula === '' || $password === '') {
            $error = 'Ingresa tu cédula y contraseña.';
        } else {
            try {
                $paciente = paciente_buscar_por_cedula($cedula);

                if ($paciente && !empty($paciente['password_hash']) && password_verify($password, $paciente['password_hash'])) {
                    session_regenerate_id(true);
                    $_SESSION['paciente_id'] = (string) $paciente['id'];
                    $_SESSION['paciente_nombre'] = $paciente['nombre'];
                    redirect($next);
                }

                $error = 'No pudimos iniciar sesión con esos datos. Verifica tu cédula y contraseña.';
            } catch (PDOException $e) {
                error_log('Error al iniciar sesión de paciente: ' . $e->getMessage());
                $error = 'No se pudo iniciar sesión. Inténtalo nuevamente más tarde.';
            }
        }
    }

    $csrfToken = token_csrf_paciente();
    render('paciente/login', compact('error', 'csrfToken', 'next'));
}

function paciente_registro(): void
{
    iniciar_sesion();

    $modoAdmin = (($_GET['desde'] ?? $_POST['desde'] ?? '') === 'admin') && recepcionista_autenticado();

    if (!$modoAdmin && paciente_autenticado()) {
        redirect('perfil-paciente');
    }

    $error = '';
    $csrfToken = token_csrf_paciente();
    $catalogoAlergias = paciente_catalogo_alergias();

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
        $passwordConfirmacion = is_string($_POST['password_confirmacion'] ?? null) ? $_POST['password_confirmacion'] : '';
        $aceptoTerminos = ($_POST['acepto_terminos'] ?? '') === '1';

        $nombreVal = $nombre;
        $cedulaVal = $cedula;
        $telefonoVal = $telefono;
        $correoVal = $correo;
        $fechaNacVal = $fechaNac;
        $alergiaSelectVal = array_key_exists($alergiaSelect, $catalogoAlergias) ? $alergiaSelect : 'Otra';
        $alergiaManualVal = $alergiaManual;

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

        $fechaObj = DateTimeImmutable::createFromFormat('!Y-m-d', $fechaNac);
        $fechaNacValida = $fechaObj !== false
            && $fechaObj->format('Y-m-d') === $fechaNac
            && $fechaNac <= date('Y-m-d')
            && $fechaNac >= '1900-01-01';

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
                $pacienteExistente = paciente_buscar_por_cedula($cedula);
                $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                $pacienteId = 0;

                if ($pacienteExistente && !empty($pacienteExistente['password_hash'])) {
                    $error = 'Ya existe una cuenta con esa cédula. Inicia sesión.';
                } elseif ($pacienteExistente) {
                    $datosCoinciden = hash_equals((string) $pacienteExistente['telefono'], $telefono)
                        && strtolower((string) $pacienteExistente['email']) === strtolower($correo)
                        && mb_strtolower((string) $pacienteExistente['nombre'], 'UTF-8') === mb_strtolower($nombre, 'UTF-8');

                    if (!$datosCoinciden) {
                        $error = 'No pudimos verificar tus datos. Revisa el teléfono y correo registrados o comunícate con recepción.';
                    } elseif (paciente_activar_cuenta((int) $pacienteExistente['id'], $passwordHash, $fechaNac, $alergiasFinal) === 1) {
                        $pacienteId = (int) $pacienteExistente['id'];
                    } else {
                        $error = 'La cuenta ya fue activada. Inicia sesión con tu cédula y contraseña.';
                    }
                } else {
                    $pacienteId = paciente_crear([
                        'nombre' => $nombre,
                        'cedula' => $cedula,
                        'telefono' => $telefono,
                        'email' => $correo,
                        'fecha_nac' => $fechaNac,
                        'alergias' => $alergiasFinal,
                        'password_hash' => $passwordHash,
                    ]);
                }

                if ($error === '') {
                    if ($modoAdmin) {
                        flash_set('Paciente registrado correctamente con consentimiento de datos informado.', 'exito');
                        redirect('pacientes');
                    }

                    session_regenerate_id(true);
                    $_SESSION['paciente_id'] = (string) $pacienteId;
                    $_SESSION['paciente_nombre'] = $nombre;
                    paciente_flash_set('¡Bienvenido(a) a DENTIX! Tu cuenta e historia clínica inicial han sido creadas bajo nuestra Política de Protección de Datos de Salud.');
                    redirect('perfil-paciente');
                }
            } catch (PDOException $e) {
                error_log('Error al registrar cuenta de paciente: ' . $e->getMessage());
                $error = 'No se pudo crear tu cuenta. Verifica que el nombre inicie cada palabra con mayúscula (ej. Juan Steven) y que los datos sean válidos.';
            }
        }
    }

    render('paciente/form', compact(
        'modoAdmin',
        'error',
        'csrfToken',
        'catalogoAlergias',
        'nombreVal',
        'cedulaVal',
        'telefonoVal',
        'correoVal',
        'fechaNacVal',
        'alergiaSelectVal',
        'alergiaManualVal'
    ));
}

function paciente_portal(): void
{
    iniciar_sesion();

    if (!paciente_autenticado()) {
        redirect('login-paciente', ['next' => 'portal-paciente']);
    }

    header('Cache-Control: no-store, private');
    header('Pragma: no-cache');

    $error = '';
    $servicios = [];
    $paciente = null;

    try {
        $paciente = paciente_buscar_por_id((int) $_SESSION['paciente_id']);

        if (!$paciente) {
            cerrar_sesion_actual();
            redirect('login-paciente');
        }

        $servicios = servicio_listar_ordenado();
    } catch (PDOException $e) {
        error_log('Error al cargar opciones de cita: ' . $e->getMessage());
        http_response_code(500);
        exit('No se pudo cargar el portal de citas. Inténtalo nuevamente más tarde.');
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $servicioId = is_string($_POST['servicio_id'] ?? null) ? trim($_POST['servicio_id']) : '';
        $fecha = is_string($_POST['fecha'] ?? null) ? trim($_POST['fecha']) : '';
        $hora = is_string($_POST['hora'] ?? null) ? trim($_POST['hora']) : '';

        if (!validar_csrf_paciente()) {
            $error = 'La sesión del formulario venció. Recarga la página e inténtalo de nuevo.';
        } elseif (!ctype_digit($servicioId)) {
            $error = 'Selecciona un servicio válido.';
        } else {
            $fechaIngresada = DateTimeImmutable::createFromFormat('!Y-m-d', $fecha);
            $fechaValida = $fechaIngresada !== false
                && $fechaIngresada->format('Y-m-d') === $fecha
                && $fecha >= date('Y-m-d');
            $horaValida = preg_match('/^(?:0[89]|1[0-9]):(?:00|30)$/', $hora) === 1;

            if (!$fechaValida || !$horaValida) {
                $error = 'Elige una fecha válida y un horario entre 08:00 y 19:30, cada 30 minutos.';
            } else {
                try {
                    if (!servicio_existe((int) $servicioId)) {
                        $error = 'El servicio seleccionado ya no está disponible.';
                    } else {
                        $citaCercana = cita_cercana_paciente((int) $paciente['id'], $fecha);

                        if ($citaCercana) {
                            if ($citaCercana['fecha'] === $fecha) {
                                $error = 'No puedes tener más de una cita el mismo día.';
                            } else {
                                $error = 'Deben transcurrir al menos 4 días completos entre citas. Ya tienes una cita programada o realizada el ' . e($citaCercana['fecha']) . '.';
                            }
                        } else {
                            cita_guardar([
                                'paciente_id' => (int) $paciente['id'],
                                'odontologo_id' => null,
                                'servicio_id' => (int) $servicioId,
                                'recepcionista_id' => null,
                                'fecha' => $fecha,
                                'hora' => $hora,
                                'motivo' => '',
                                'estado' => 'pendiente',
                            ]);
                            paciente_flash_set('Tu solicitud quedó pendiente. Recepción confirmará la cita y asignará al odontólogo.');
                            redirect('perfil-paciente');
                        }
                    }
                } catch (PDOException $e) {
                    error_log('Error al crear cita desde el portal del paciente: ' . $e->getMessage());
                    $error = 'No se pudo guardar la cita. Verifica los datos o intenta con otro horario.';
                }
            }
        }
    }

    $csrfToken = token_csrf_paciente();
    $selectedServicioId = is_string($_POST['servicio_id'] ?? null) ? $_POST['servicio_id'] : '';
    $fechaIngresadaForm = is_string($_POST['fecha'] ?? null) ? $_POST['fecha'] : '';
    $horaIngresadaForm = is_string($_POST['hora'] ?? null) ? $_POST['hora'] : '';

    render('paciente/portal', compact(
        'error',
        'servicios',
        'paciente',
        'csrfToken',
        'selectedServicioId',
        'fechaIngresadaForm',
        'horaIngresadaForm'
    ));
}

function paciente_perfil(): void
{
    iniciar_sesion();

    if (!paciente_autenticado()) {
        redirect('login-paciente');
    }

    header('Cache-Control: no-store, private');
    header('Pragma: no-cache');

    try {
        $paciente = paciente_buscar_por_id((int) $_SESSION['paciente_id']);

        if (!$paciente) {
            cerrar_sesion_actual();
            redirect('login-paciente');
        }

        $citas = paciente_citas((int) $paciente['id']);
    } catch (PDOException $e) {
        error_log('Error al cargar perfil del paciente: ' . $e->getMessage());
        http_response_code(500);
        exit('No se pudo cargar tu perfil. Inténtalo nuevamente más tarde.');
    }

    $totalCitas = count($citas);
    $pendientes = count(array_filter($citas, static fn(array $cita): bool => $cita['estado'] === 'pendiente'));
    $confirmadas = count(array_filter($citas, static fn(array $cita): bool => $cita['estado'] === 'confirmada'));
    $flash = paciente_flash_get();
    $csrfToken = token_csrf_paciente();

    $estadoLabels = [
        'pendiente' => 'Pendiente',
        'confirmada' => 'Confirmada',
        'completada' => 'Completada',
        'cancelada' => 'Cancelada',
        'no_show' => 'No asistió',
    ];

    render('paciente/perfil', compact(
        'paciente',
        'citas',
        'totalCitas',
        'pendientes',
        'confirmadas',
        'flash',
        'csrfToken',
        'estadoLabels'
    ));
}

function paciente_agendar(): void
{
    iniciar_sesion();

    if (!paciente_autenticado()) {
        redirect('login-paciente', ['next' => 'portal-paciente']);
    }

    redirect('portal-paciente');
}

function paciente_logout(): void
{
    iniciar_sesion();

    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !validar_csrf_paciente()) {
        http_response_code(400);
        exit('Solicitud no válida.');
    }

    cerrar_sesion_actual();
    redirect('login-paciente');
}

function paciente_index(): void
{
    requerir_recepcionista('login-recepcionista');

    [$mensaje, $tipo_mensaje] = flash_get();

    if ($mensaje === null && ($_GET['registro'] ?? '') === 'exito') {
        $mensaje = 'Paciente registrado correctamente.';
        $tipo_mensaje = 'exito';
    }

    try {
        $pacientes = paciente_listar();
    } catch (PDOException $e) {
        error_log('Error al listar pacientes: ' . $e->getMessage());
        $pacientes = [];
    }

    render('paciente/lista', compact('pacientes', 'mensaje', 'tipo_mensaje'));
}

function paciente_editar(): void
{
    requerir_recepcionista('login-recepcionista');

    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

    if ($id === false || $id === null || $id <= 0) {
        flash_set('Error: el ID del paciente no es válido.', 'error');
        redirect('pacientes');
    }

    try {
        $paciente = paciente_buscar_por_id($id);
    } catch (PDOException $e) {
        error_log('Error al consultar paciente: ' . $e->getMessage());
        $paciente = null;
    }

    if (!$paciente) {
        flash_set('No se encontró el paciente solicitado.', 'error');
        redirect('pacientes');
    }

    [$mensaje, $tipo_mensaje] = flash_get();
    $csrfToken = token_csrf_recepcionista();

    render('paciente/editar', compact('paciente', 'mensaje', 'tipo_mensaje', 'csrfToken'));
}

function paciente_procesar(): void
{
    requerir_recepcionista('login-recepcionista');

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        redirect('pacientes');
    }

    if (!validar_csrf_recepcionista()) {
        flash_set('La sesión del formulario venció. Recarga la página e inténtalo de nuevo.', 'error');
        redirect('pacientes');
    }

    $accion = $_POST['accion'] ?? 'registrar';

    if ($accion === 'actualizar') {
        $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
        $nombre = trim($_POST['nombre'] ?? '');
        $cedula = trim($_POST['cedula'] ?? '');
        $telefono = trim($_POST['telefono'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $fecha_nac = trim($_POST['fecha_nac'] ?? '');
        $alergias = trim($_POST['alergias'] ?? '');

        if ($id === false || $id === null || $id <= 0) {
            flash_set('Error: el ID del paciente no es válido.', 'error');
            redirect('pacientes');
        }

        if ($nombre === '' || $cedula === '' || $telefono === '') {
            flash_set('Nombre, cédula y teléfono son obligatorios.', 'error');
            redirect('pacientes/editar', ['id' => $id]);
        }

        if (preg_match('/^[A-ZÁÉÍÓÚÜÑ][a-záéíóúüñ]+(?: [A-ZÁÉÍÓÚÜÑ][a-záéíóúüñ]+)*$/u', $nombre) !== 1) {
            flash_set('Cada palabra del nombre debe empezar con mayúscula seguida de minúsculas (ejemplo: Juan Steven).', 'error');
            redirect('pacientes/editar', ['id' => $id]);
        }

        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            flash_set('El correo electrónico ingresado no es válido.', 'error');
            redirect('pacientes/editar', ['id' => $id]);
        }

        if ($fecha_nac !== '' && $fecha_nac > date('Y-m-d')) {
            flash_set('La fecha de nacimiento no puede ser futura.', 'error');
            redirect('pacientes/editar', ['id' => $id]);
        }

        try {
            if (paciente_cedula_duplicada($cedula, $id)) {
                flash_set('Ya existe otro paciente registrado con esa cédula.', 'error');
                redirect('pacientes/editar', ['id' => $id]);
            }

            paciente_actualizar($id, [
                'nombre' => $nombre,
                'cedula' => $cedula,
                'telefono' => $telefono,
                'email' => $email,
                'fecha_nac' => $fecha_nac,
                'alergias' => $alergias,
            ]);

            flash_set('Paciente actualizado correctamente.', 'exito');
            redirect('pacientes');
        } catch (PDOException $e) {
            error_log('Error al actualizar paciente: ' . $e->getMessage());
            flash_set('No se pudo actualizar el paciente. Verifica los datos e inténtalo nuevamente.', 'error');
            redirect('pacientes/editar', ['id' => $id]);
        }
    }

    $nombre = trim($_POST['nombre'] ?? '');
    $cedula = trim($_POST['cedula'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $fecha_nac = trim($_POST['fecha_nac'] ?? '');
    $alergias = trim($_POST['alergias'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($nombre === '' || $cedula === '' || $telefono === '') {
        flash_set('Completa los campos obligatorios (Nombre, Cédula y Teléfono).', 'error');
        redirect('pacientes');
    }

    try {
        paciente_crear([
            'nombre' => $nombre,
            'cedula' => $cedula,
            'telefono' => $telefono,
            'email' => $email,
            'fecha_nac' => $fecha_nac,
            'alergias' => $alergias,
            'password_hash' => $password !== '' ? password_hash($password, PASSWORD_DEFAULT) : null,
        ]);
        flash_set('Paciente registrado correctamente.', 'exito');
    } catch (PDOException $e) {
        error_log('Error al registrar paciente: ' . $e->getMessage());
        flash_set('No se pudo registrar el paciente (verifica que la cédula no esté duplicada).', 'error');
    }

    redirect('pacientes');
}
