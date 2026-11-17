<?php

declare(strict_types=1);

require_once APP_PATH . '/models/cita_model.php';
require_once APP_PATH . '/models/paciente_model.php';
require_once APP_PATH . '/models/odontologo_model.php';
require_once APP_PATH . '/models/servicio_model.php';

function cita_index(): void
{
    requerir_recepcionista('login-recepcionista');

    $mensaje = '';
    $error = '';
    [$flash, $tipoFlash] = flash_get();

    if ($flash !== null) {
        if ($tipoFlash === 'error') {
            $error = (string) $flash;
        } else {
            $mensaje = (string) $flash;
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['actualizar_cita'])) {
        $citaId = filter_var($_POST['cita_id'] ?? null, FILTER_VALIDATE_INT);
        $odontologoId = filter_var($_POST['odontologo_id'] ?? null, FILTER_VALIDATE_INT);

        if (!validar_csrf_recepcionista()) {
            $error = 'La sesión del formulario venció. Recarga la página e inténtalo de nuevo.';
        } elseif ($citaId === false || $citaId <= 0 || $odontologoId === false || $odontologoId <= 0) {
            $error = 'Selecciona un odontólogo válido para confirmar la cita.';
        } else {
            try {
                $odontologo = odontologo_buscar_por_id($odontologoId);

                if (!$odontologo || !$odontologo['activo']) {
                    $error = 'El odontólogo seleccionado no está disponible.';
                } else {
                    $cita = cita_buscar_por_id($citaId);

                    if (!$cita) {
                        $error = 'No se encontró la cita solicitada.';
                    } elseif (cita_odontologo_tiene_conflicto($odontologoId, (string) $cita['fecha'], $citaId)) {
                        $error = "El odontólogo seleccionado ya tiene una cita asignada para el día {$cita['fecha']}. Cada odontólogo solo puede atender máximo 1 cita por día.";
                    } elseif (cita_confirmar($citaId, $odontologoId, (int) $_SESSION['recepcionista_id'])) {
                        $mensaje = 'La cita fue confirmada y el odontólogo quedó asignado.';
                    } else {
                        $error = 'La cita ya no está pendiente. Actualiza la página para ver su estado.';
                    }
                }
            } catch (PDOException $e) {
                error_log('Error al confirmar y asignar una cita: ' . $e->getMessage());
                $error = 'No se pudo confirmar la cita. Verifica que el odontólogo no tenga otra cita en ese horario.';
            }
        }
    }

    try {
        $citasPendientes = cita_listar_pendientes();
    } catch (PDOException $e) {
        $citasPendientes = [];
        $error = 'Error al cargar las solicitudes pendientes.';
        error_log('Error al cargar citas pendientes: ' . $e->getMessage());
    }

    try {
        $citasConfirmadas = cita_listar_confirmadas();
    } catch (PDOException $e) {
        $citasConfirmadas = [];
        $error = 'Error al cargar las citas confirmadas.';
        error_log('Error al cargar citas confirmadas: ' . $e->getMessage());
    }

    try {
        $odontologosActivos = odontologo_listar_activos();
    } catch (PDOException $e) {
        $odontologosActivos = [];
        $error = 'Error al cargar odontólogos disponibles.';
        error_log('Error al cargar odontólogos activos: ' . $e->getMessage());
    }

    $csrfToken = token_csrf_recepcionista();

    render('cita/lista', compact(
        'mensaje',
        'error',
        'citasPendientes',
        'citasConfirmadas',
        'odontologosActivos',
        'csrfToken'
    ));
}

function cita_crear(): void
{
    requerir_recepcionista('login-recepcionista');

    [$mensaje, $tipo_mensaje] = flash_get();

    try {
        $pacientes = db()->query('SELECT id, nombre, cedula FROM paciente ORDER BY nombre')->fetchAll();
        $odontologos = odontologo_listar_activos();
        $servicios = servicio_listar_ordenado();
    } catch (PDOException $e) {
        error_log('Error al cargar datos para nueva cita: ' . $e->getMessage());
        $pacientes = $odontologos = $servicios = [];
    }

    $csrfToken = token_csrf_recepcionista();

    render('cita/form', compact('mensaje', 'tipo_mensaje', 'pacientes', 'odontologos', 'servicios', 'csrfToken'));
}

function cita_editar(): void
{
    requerir_recepcionista('login-recepcionista');

    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

    if ($id === false || $id === null || $id <= 0) {
        flash_set('Error: el ID de la cita no es válido.', 'error');
        redirect('citas');
    }

    $error = '';

    try {
        $cita = cita_buscar_por_id($id);

        if (!$cita) {
            flash_set('No se encontró la cita solicitada.', 'error');
            redirect('citas');
        }

        $pacientes = db()->query('SELECT id, nombre, cedula FROM paciente ORDER BY nombre ASC')->fetchAll();
        $odontologos = odontologo_listar_activos();
        $servicios = servicio_listar_ordenado();
    } catch (PDOException $e) {
        error_log('Error al consultar cita: ' . $e->getMessage());
        $error = 'No se pudieron cargar los datos de la cita.';
        $pacientes = $odontologos = $servicios = [];
        $cita = null;
    }

    [$mensaje, $tipo_mensaje] = flash_get();
    $csrfToken = token_csrf_recepcionista();

    render('cita/editar', compact(
        'cita',
        'pacientes',
        'odontologos',
        'servicios',
        'mensaje',
        'tipo_mensaje',
        'error',
        'csrfToken'
    ));
}

function cita_procesar(): void
{
    requerir_recepcionista('login-recepcionista');

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        redirect('citas');
    }

    if (!validar_csrf_recepcionista()) {
        flash_set('La sesión del formulario venció. Recarga la página e inténtalo de nuevo.', 'error');
        redirect('citas');
    }

    $accion = $_POST['accion'] ?? 'registrar';
    $recepcionistaId = (int) $_SESSION['recepcionista_id'];

    if ($accion === 'cancelar') {
        $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
        $motivoCancelacion = trim($_POST['motivo_cancelacion'] ?? '');

        if ($id === false || $id === null || $id <= 0) {
            flash_set('Error: el ID de la cita no es válido.', 'error');
        } elseif ($motivoCancelacion === '') {
            flash_set('Debes indicar el motivo de cancelación.', 'error');
        } else {
            try {
                if (cita_cancelar($id, $recepcionistaId, $motivoCancelacion)) {
                    flash_set('La cita fue cancelada correctamente.', 'exito');
                } else {
                    flash_set('Solo se pueden cancelar citas en estado pendiente o confirmada.', 'error');
                }
            } catch (PDOException $e) {
                error_log('Error al cancelar cita: ' . $e->getMessage());
                flash_set(cita_traducir_error($e, 'No se pudo cancelar la cita.'), 'error');
            }
        }

        redirect('citas');
    }

    if ($accion === 'actualizar') {
        $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
        $pacienteId = filter_var($_POST['paciente_id'] ?? null, FILTER_VALIDATE_INT);
        $odontologoRaw = trim((string) ($_POST['odontologo_id'] ?? ''));
        $odontologoId = $odontologoRaw === '' ? null : filter_var($odontologoRaw, FILTER_VALIDATE_INT);
        $servicioId = filter_var($_POST['servicio_id'] ?? null, FILTER_VALIDATE_INT);
        $fecha = trim($_POST['fecha'] ?? '');
        $hora = substr(trim($_POST['hora'] ?? ''), 0, 5);
        $estado = trim($_POST['estado'] ?? '');
        $motivo = trim($_POST['motivo'] ?? '');
        $motivoCancelacion = trim($_POST['motivo_cancelacion'] ?? '');

        $estadosValidos = ['pendiente', 'confirmada', 'completada', 'cancelada', 'no_show'];
        $fechaObj = DateTimeImmutable::createFromFormat('!Y-m-d', $fecha);
        $fechaValida = $fechaObj !== false && $fechaObj->format('Y-m-d') === $fecha;
        $horaValida = preg_match('/^(?:0[89]|1[0-9]):(?:00|30)$/', $hora) === 1;

        if ($id === false || $id === null || $id <= 0) {
            flash_set('Error: el ID de la cita no es válido.', 'error');
            redirect('citas');
        }

        if ($pacienteId === false || $pacienteId <= 0 || $servicioId === false || $servicioId <= 0) {
            flash_set('Selecciona un paciente y un servicio válidos.', 'error');
            redirect('citas/editar', ['id' => $id]);
        }

        if ($odontologoId === false || ($odontologoId !== null && $odontologoId <= 0)) {
            flash_set('El odontólogo seleccionado no es válido.', 'error');
            redirect('citas/editar', ['id' => $id]);
        }

        if (!in_array($estado, $estadosValidos, true)) {
            flash_set('El estado de la cita no es válido.', 'error');
            redirect('citas/editar', ['id' => $id]);
        }

        if (in_array($estado, ['confirmada', 'completada'], true) && $odontologoId === null) {
            flash_set('Para confirmar o completar una cita debes asignar un odontólogo.', 'error');
            redirect('citas/editar', ['id' => $id]);
        }

        if (!$fechaValida || !$horaValida) {
            flash_set('Elige una fecha válida y un horario entre 08:00 y 19:30 en intervalos de 30 minutos.', 'error');
            redirect('citas/editar', ['id' => $id]);
        }

        if ($estado === 'cancelada' && $motivoCancelacion === '') {
            flash_set('Para cambiar el estado a cancelada debes escribir el motivo de cancelación.', 'error');
            redirect('citas/editar', ['id' => $id]);
        }

        try {
            cita_actualizar($id, [
                'paciente_id' => $pacienteId,
                'odontologo_id' => $odontologoId,
                'servicio_id' => $servicioId,
                'recepcionista_id' => $recepcionistaId,
                'fecha' => $fecha,
                'hora' => $hora,
                'estado' => $estado,
                'motivo' => $motivo,
                'motivo_cancelacion' => $motivoCancelacion,
            ]);

            flash_set('¡Cita actualizada correctamente!', 'exito');
            redirect('citas');
        } catch (PDOException $e) {
            error_log('Error al actualizar cita: ' . $e->getMessage());
            flash_set(cita_traducir_error($e, 'No se pudo actualizar la cita. Verifica los datos y reglas de agenda.'), 'error');
            redirect('citas/editar', ['id' => $id]);
        }
    }

    $pacienteId = filter_var($_POST['paciente_id'] ?? null, FILTER_VALIDATE_INT);
    $odontologoRaw = trim((string) ($_POST['odontologo_id'] ?? ''));
    $odontologoId = $odontologoRaw === '' ? null : filter_var($odontologoRaw, FILTER_VALIDATE_INT);
    $servicioId = filter_var($_POST['servicio_id'] ?? null, FILTER_VALIDATE_INT);
    $fecha = trim($_POST['fecha'] ?? '');
    $hora = substr(trim($_POST['hora'] ?? ''), 0, 5);
    $motivo = trim($_POST['motivo'] ?? '');

    $fechaObj = DateTimeImmutable::createFromFormat('!Y-m-d', $fecha);
    $fechaValida = $fechaObj !== false && $fechaObj->format('Y-m-d') === $fecha;
    $horaValida = preg_match('/^(?:0[89]|1[0-9]):(?:00|30)$/', $hora) === 1;

    if ($pacienteId === false || $pacienteId <= 0 || $servicioId === false || $servicioId <= 0 || $odontologoId === false) {
        flash_set('Selecciona paciente, servicio y odontólogo válidos.', 'error');
        redirect('citas/nueva');
    }

    if (!$fechaValida || !$horaValida) {
        flash_set('Elige una fecha válida y un horario entre 08:00 y 19:30 en intervalos de 30 minutos.', 'error');
        redirect('citas/nueva');
    }

    try {
        $estadoInicial = $odontologoId !== null ? 'confirmada' : 'pendiente';

        cita_guardar([
            'paciente_id' => $pacienteId,
            'odontologo_id' => $odontologoId,
            'servicio_id' => $servicioId,
            'recepcionista_id' => $recepcionistaId,
            'fecha' => $fecha,
            'hora' => $hora,
            'motivo' => $motivo,
            'estado' => $estadoInicial,
        ]);

        flash_set(
            $estadoInicial === 'confirmada'
                ? '¡Cita agendada y confirmada exitosamente!'
                : '¡Cita registrada como pendiente exitosamente!',
            'exito'
        );
        redirect('citas');
    } catch (PDOException $e) {
        error_log('Error al agendar cita desde recepción: ' . $e->getMessage());
        flash_set(cita_traducir_error($e, 'Error al agendar la cita. Verifica las reglas de disponibilidad.'), 'error');
        redirect('citas/nueva');
    }
}
