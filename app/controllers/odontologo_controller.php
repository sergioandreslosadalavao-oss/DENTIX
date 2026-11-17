<?php

declare(strict_types=1);

require_once APP_PATH . '/models/odontologo_model.php';
require_once APP_PATH . '/models/cita_model.php';

function odontologo_login(): void
{
    iniciar_sesion();

    if (odontologo_autenticado()) {
        redirect('panel-odontologo');
    }

    $error = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $email = trim($_POST['email'] ?? '');
        $password = trim($_POST['password'] ?? '');

        if (!validar_csrf_odontologo()) {
            $error = 'La sesión del formulario venció. Recarga la página e inténtalo de nuevo.';
        } elseif ($email === '' || $password === '') {
            $error = 'Por favor diligencie su correo electrónico y contraseña.';
        } else {
            try {
                $odontologo = odontologo_buscar_por_email($email);

                if ($odontologo && password_verify($password, $odontologo['password_hash'])) {
                    session_regenerate_id(true);
                    $_SESSION['odontologo_id'] = (string) $odontologo['id'];
                    $_SESSION['odontologo_nombre'] = $odontologo['nombre'];
                    $_SESSION['odontologo_especialidad'] = $odontologo['especialidad'];
                    $_SESSION['odontologo_consultorio'] = $odontologo['consultorio'] ?? 'Consultorio asignado';

                    odontologo_actualizar_ultimo_acceso((int) $odontologo['id']);
                    redirect('panel-odontologo');
                }

                $error = 'Credenciales incorrectas o cuenta inactiva. Verifique correo y contraseña.';
            } catch (PDOException $e) {
                error_log('Error al iniciar sesión de odontólogo: ' . $e->getMessage());
                $error = 'Error al conectar con la base de datos.';
            }
        }
    }

    $csrfToken = token_csrf_odontologo();
    render('odontologo/login', compact('error', 'csrfToken'));
}

function odontologo_datos_sesion(): array
{
    $nombre = $_SESSION['odontologo_nombre'] ?? 'Especialista';

    return [
        'odontologoId' => (int) ($_SESSION['odontologo_id'] ?? 0),
        'nombreOdontologo' => $nombre,
        'especialidadOdontologo' => $_SESSION['odontologo_especialidad'] ?? 'Odontología',
        'consultorioOdontologo' => $_SESSION['odontologo_consultorio'] ?? 'Consultorio',
        'inicialOdontologo' => mb_strtoupper(mb_substr(preg_replace('/^(Dra?\.\s+)/i', '', $nombre), 0, 1, 'UTF-8'), 'UTF-8'),
    ];
}

function odontologo_filtrar_historial(array $citas, string $busqueda): array
{
    if ($busqueda === '') {
        return $citas;
    }

    return array_values(array_filter($citas, static function (array $item) use ($busqueda): bool {
        $needle = mb_strtolower($busqueda, 'UTF-8');

        return str_contains(mb_strtolower($item['paciente_nombre'] ?? '', 'UTF-8'), $needle)
            || str_contains(mb_strtolower($item['paciente_cedula'] ?? '', 'UTF-8'), $needle)
            || str_contains(mb_strtolower($item['servicio_nombre'] ?? '', 'UTF-8'), $needle)
            || str_contains(mb_strtolower($item['procedimiento'] ?? '', 'UTF-8'), $needle);
    }));
}

function odontologo_panel(): void
{
    requerir_odontologo('login-odontologo');

    $datosSesion = odontologo_datos_sesion();
    extract($datosSesion);

    $mensaje = '';
    $error = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['finalizar_cita'])) {
        $citaId = filter_input(INPUT_POST, 'cita_id', FILTER_VALIDATE_INT);
        $detalle = trim($_POST['detalle'] ?? '');
        $procedimiento = trim($_POST['procedimiento'] ?? '');
        $notaClinica = trim($_POST['nota_clinica'] ?? '');
        $fechaSalida = trim($_POST['fecha_salida'] ?? '');
        $horaSalida = trim($_POST['hora_salida'] ?? '');

        if (!validar_csrf_odontologo()) {
            $error = 'La sesión del formulario venció. Recarga la página e inténtalo de nuevo.';
        } elseif ($citaId === false || $citaId <= 0) {
            $error = 'ID de cita no válido.';
        } elseif ($detalle === '' || $procedimiento === '' || $fechaSalida === '' || $horaSalida === '') {
            $error = 'Por favor completa la fecha y hora de salida, el resumen de la cita y el procedimiento realizado.';
        } else {
            try {
                $finalizada = odontologo_finalizar_cita(
                    $citaId,
                    $odontologoId,
                    $detalle,
                    $procedimiento,
                    $notaClinica,
                    $fechaSalida,
                    $horaSalida
                );

                if ($finalizada) {
                    $mensaje = '¡Cita marcada como FINALIZADA y detalle clínico con hora de salida registrado!';
                } else {
                    $error = 'No se pudo finalizar la cita. Verifica que siga en estado confirmada y te pertenezca.';
                }
            } catch (PDOException $e) {
                error_log('Error al finalizar cita del odontólogo: ' . $e->getMessage());
                $error = 'Ocurrió un error al guardar la finalización y detalle clínico en la base de datos.';
            }
        }
    }

    try {
        $citasActivas = odontologo_citas_activas($odontologoId);
    } catch (PDOException $e) {
        $citasActivas = [];
        error_log('Error al cargar citas activas del odontólogo: ' . $e->getMessage());
    }

    try {
        $citasFinalizadas = odontologo_citas_finalizadas($odontologoId);
    } catch (PDOException $e) {
        $citasFinalizadas = [];
        error_log('Error al cargar citas finalizadas del odontólogo: ' . $e->getMessage());
    }

    $busquedaHistorial = trim($_GET['buscar_historial'] ?? '');
    $citasFinalizadasFiltradas = odontologo_filtrar_historial($citasFinalizadas, $busquedaHistorial);
    $csrfToken = token_csrf_odontologo();

    render('odontologo/panel', compact(
        'odontologoId',
        'nombreOdontologo',
        'especialidadOdontologo',
        'consultorioOdontologo',
        'inicialOdontologo',
        'mensaje',
        'error',
        'citasActivas',
        'citasFinalizadas',
        'citasFinalizadasFiltradas',
        'busquedaHistorial',
        'csrfToken'
    ));
}

function odontologo_historial(): void
{
    requerir_odontologo('login-odontologo');

    $datosSesion = odontologo_datos_sesion();
    extract($datosSesion);

    $busquedaHistorial = trim($_GET['buscar'] ?? '');

    try {
        $citasFinalizadas = odontologo_citas_finalizadas($odontologoId);
    } catch (PDOException $e) {
        $citasFinalizadas = [];
        error_log('Error al cargar nube de historial de citas: ' . $e->getMessage());
    }

    $citasFiltradas = odontologo_filtrar_historial($citasFinalizadas, $busquedaHistorial);
    $csrfToken = token_csrf_odontologo();

    render('odontologo/historial', compact(
        'odontologoId',
        'nombreOdontologo',
        'especialidadOdontologo',
        'consultorioOdontologo',
        'inicialOdontologo',
        'citasFinalizadas',
        'citasFiltradas',
        'busquedaHistorial',
        'csrfToken'
    ));
}

function odontologo_logout(): void
{
    iniciar_sesion();

    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !validar_csrf_odontologo()) {
        http_response_code(400);
        exit('Solicitud no válida.');
    }

    cerrar_sesion_actual();
    redirect('login-odontologo');
}

function odontologo_index(): void
{
    requerir_recepcionista('login-recepcionista');

    [$mensaje, $tipo_mensaje] = flash_get();

    try {
        $odontologos = odontologo_listar_con_citas();
    } catch (PDOException $e) {
        error_log('Error al listar odontólogos: ' . $e->getMessage());
        $odontologos = [];
    }

    $csrfToken = token_csrf_recepcionista();
    render('odontologo/lista', compact('odontologos', 'mensaje', 'tipo_mensaje', 'csrfToken'));
}

function odontologo_crear(): void
{
    requerir_recepcionista('login-recepcionista');

    $error = '';
    $nombre = '';
    $especialidad = '';
    $consultorio = '';
    $email = '';
    $csrfToken = token_csrf_recepcionista();

    render('odontologo/form', compact('error', 'nombre', 'especialidad', 'consultorio', 'email', 'csrfToken'));
}

function odontologo_editar(): void
{
    requerir_recepcionista('login-recepcionista');

    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

    if ($id === false || $id === null || $id <= 0) {
        flash_set('Error: el ID del odontólogo no es válido.', 'error');
        redirect('odontologos');
    }

    try {
        $odontologo = odontologo_buscar_por_id($id);
    } catch (PDOException $e) {
        error_log('Error al consultar odontólogo: ' . $e->getMessage());
        $odontologo = null;
    }

    if (!$odontologo) {
        flash_set('No se encontró el odontólogo solicitado.', 'error');
        redirect('odontologos');
    }

    [$mensaje, $tipo_mensaje] = flash_get();
    $csrfToken = token_csrf_recepcionista();

    render('odontologo/editar', compact('odontologo', 'mensaje', 'tipo_mensaje', 'csrfToken'));
}

function odontologo_procesar(): void
{
    requerir_recepcionista('login-recepcionista');

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        redirect('odontologos');
    }

    if (!validar_csrf_recepcionista()) {
        flash_set('La sesión del formulario venció. Recarga la página e inténtalo de nuevo.', 'error');
        redirect('odontologos');
    }

    $accion = $_POST['accion'] ?? 'registrar';

    try {
        if ($accion === 'registrar') {
            $nombre = trim($_POST['nombre'] ?? '');
            $especialidad = trim($_POST['especialidad'] ?? '');
            $consultorio = trim($_POST['consultorio'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $error = '';

            if ($nombre === '' || $especialidad === '' || $email === '' || $password === '') {
                $error = 'Por favor, completa los campos obligatorios (Nombre, Especialidad, Correo y Contraseña).';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = 'Ingresa un correo electrónico válido.';
            } elseif (strlen($password) < 6 || strlen($password) > 72) {
                $error = 'La contraseña debe tener entre 6 y 72 caracteres.';
            } else {
                $slug = odontologo_generar_slug($nombre);

                if ($slug === '') {
                    $error = 'No se pudo generar un identificador público válido a partir del nombre.';
                } elseif (odontologo_slug_existe($slug)) {
                    $error = 'Ya existe un odontólogo con un nombre que genera el mismo identificador público.';
                } elseif (odontologo_email_existe($email)) {
                    $error = 'Ya existe un odontólogo registrado con ese correo electrónico.';
                } else {
                    odontologo_guardar([
                        'nombre' => $nombre,
                        'especialidad' => $especialidad,
                        'consultorio' => $consultorio,
                        'slug_publico' => $slug,
                        'email' => $email,
                        'password_hash' => password_hash($password, PASSWORD_DEFAULT),
                    ]);
                    flash_set('Odontólogo registrado correctamente.', 'exito');
                    redirect('odontologos');
                }
            }

            $csrfToken = token_csrf_recepcionista();
            render('odontologo/form', compact('error', 'nombre', 'especialidad', 'consultorio', 'email', 'csrfToken'));
            return;
        }

        if ($accion === 'actualizar') {
            $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
            $nombre = trim($_POST['nombre'] ?? '');
            $especialidad = trim($_POST['especialidad'] ?? '');
            $consultorio = trim($_POST['consultorio'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $activo = ($_POST['activo'] ?? '1') === '1';

            if ($id === false || $id === null || $id <= 0) {
                flash_set('Error: el ID del odontólogo no es válido.', 'error');
                redirect('odontologos');
            }

            if ($nombre === '') {
                flash_set('Error: el nombre es obligatorio.', 'error');
                redirect('odontologos/editar', ['id' => $id]);
            }
            if ($especialidad === '') {
                flash_set('Error: la especialidad es obligatoria.', 'error');
                redirect('odontologos/editar', ['id' => $id]);
            }
            if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                flash_set('Error: el correo electrónico es obligatorio y debe ser válido.', 'error');
                redirect('odontologos/editar', ['id' => $id]);
            }

            $slug = odontologo_generar_slug($nombre);

            if ($slug === '') {
                flash_set('Error: no se pudo generar el slug del odontólogo.', 'error');
                redirect('odontologos/editar', ['id' => $id]);
            }

            if (odontologo_slug_existe($slug, $id)) {
                flash_set('Ya existe otro odontólogo con un nombre que genera el mismo slug.', 'error');
                redirect('odontologos/editar', ['id' => $id]);
            }

            if (odontologo_email_existe($email, $id)) {
                flash_set('Ya existe otro odontólogo con ese correo electrónico.', 'error');
                redirect('odontologos/editar', ['id' => $id]);
            }

            odontologo_actualizar($id, [
                'nombre' => $nombre,
                'especialidad' => $especialidad,
                'consultorio' => $consultorio,
                'slug_publico' => $slug,
                'email' => $email,
                'activo' => $activo,
            ]);
            flash_set('Odontólogo actualizado correctamente.', 'exito');
            redirect('odontologos');
        }

        if ($accion === 'eliminar') {
            $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);

            if ($id === false || $id === null || $id <= 0) {
                flash_set('Error: el ID del odontólogo no es válido.', 'error');
            } elseif (odontologo_eliminar($id) > 0) {
                flash_set('Odontólogo eliminado correctamente.', 'exito');
            } else {
                flash_set('No se encontró el odontólogo que intentas eliminar.', 'error');
            }

            redirect('odontologos');
        }

        flash_set('Error: la acción solicitada no es válida.', 'error');
    } catch (PDOException $e) {
        error_log('Error en odontologo_procesar: ' . $e->getMessage());

        if ($e->getCode() === '23503') {
            flash_set('No se puede eliminar el odontólogo porque tiene citas asociadas. Puedes desactivarlo desde Editar.', 'error');
        } else {
            flash_set('No se pudo completar la operación. Verifica los datos y las restricciones de la base de datos.', 'error');
        }
    }

    redirect('odontologos');
}
