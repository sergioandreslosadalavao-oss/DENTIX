<?php

declare(strict_types=1);

require_once APP_PATH . '/models/servicio_model.php';

function servicio_validar_datos(array $post): array
{
    $nombre = trim($post['nombre'] ?? '');
    $descripcion = trim($post['descripcion'] ?? '');
    $duracion = filter_var(trim($post['duracion_min'] ?? ''), FILTER_VALIDATE_INT);
    $precioTexto = str_replace(',', '.', trim($post['precio'] ?? ''));

    if ($nombre === '') {
        return [null, 'Error: el nombre es obligatorio.'];
    }

    if (preg_match_all('/./su', $nombre) > 80) {
        return [null, 'Error: el nombre no puede superar los 80 caracteres.'];
    }

    if ($duracion === false || $duracion === null) {
        return [null, 'Error: la duración debe ser un número entero de minutos.'];
    }

    if ($duracion < 15) {
        return [null, 'Error: la duración mínima es de 15 minutos.'];
    }

    if ($duracion > 1440) {
        return [null, 'Error: la duración no es válida.'];
    }

    if ($precioTexto === '') {
        $precioTexto = '0';
    }

    if (!is_numeric($precioTexto)) {
        return [null, 'Error: el precio debe ser un número.'];
    }

    $precio = round((float) $precioTexto, 2);

    if ($precio < 0) {
        return [null, 'Error: el precio no puede ser negativo.'];
    }

    if ($precio > 99999999.99) {
        return [null, 'Error: el precio es demasiado alto.'];
    }

    return [
        [
            'nombre' => $nombre,
            'descripcion' => $descripcion,
            'duracion_min' => $duracion,
            'precio' => number_format($precio, 2, '.', ''),
        ],
        null,
    ];
}

function servicio_index(): void
{
    requerir_recepcionista('login-recepcionista');

    [$mensaje, $tipo_mensaje] = flash_get();

    try {
        $servicios = servicio_listar();
    } catch (PDOException $e) {
        error_log('Error al listar servicios: ' . $e->getMessage());
        $servicios = [];
    }

    $csrfToken = token_csrf_recepcionista();
    render('servicio/lista', compact('servicios', 'mensaje', 'tipo_mensaje', 'csrfToken'));
}

function servicio_crear(): void
{
    requerir_recepcionista('login-recepcionista');

    $error = '';
    $nombre = '';
    $descripcion = '';
    $csrfToken = token_csrf_recepcionista();

    render('servicio/form', compact('error', 'nombre', 'descripcion', 'csrfToken'));
}

function servicio_editar(): void
{
    requerir_recepcionista('login-recepcionista');

    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

    if ($id === false || $id === null || $id <= 0) {
        flash_set('Error: el ID del servicio no es válido.', 'error');
        redirect('servicios');
    }

    try {
        $servicio = servicio_buscar_por_id($id);
    } catch (PDOException $e) {
        error_log('Error al consultar servicio: ' . $e->getMessage());
        $servicio = null;
    }

    if (!$servicio) {
        flash_set('No se encontró el servicio solicitado.', 'error');
        redirect('servicios');
    }

    [$mensaje, $tipo_mensaje] = flash_get();
    $csrfToken = token_csrf_recepcionista();

    render('servicio/editar', compact('servicio', 'mensaje', 'tipo_mensaje', 'csrfToken'));
}

function servicio_procesar(): void
{
    requerir_recepcionista('login-recepcionista');

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        redirect('servicios');
    }

    if (!validar_csrf_recepcionista()) {
        flash_set('La sesión del formulario venció. Recarga la página e inténtalo de nuevo.', 'error');
        redirect('servicios');
    }

    $accion = $_POST['accion'] ?? 'registrar';

    try {
        if ($accion === 'registrar') {
            [$datos, $error] = servicio_validar_datos($_POST);

            if ($error !== null) {
                $nombre = trim($_POST['nombre'] ?? '');
                $descripcion = trim($_POST['descripcion'] ?? '');
                $csrfToken = token_csrf_recepcionista();
                render('servicio/form', compact('error', 'nombre', 'descripcion', 'csrfToken'));
                return;
            }

            if (servicio_nombre_existe($datos['nombre'])) {
                $error = 'Error: ya existe un servicio con ese nombre.';
                $nombre = $datos['nombre'];
                $descripcion = $datos['descripcion'];
                $csrfToken = token_csrf_recepcionista();
                render('servicio/form', compact('error', 'nombre', 'descripcion', 'csrfToken'));
                return;
            }

            servicio_guardar($datos);
            flash_set('Servicio registrado correctamente.', 'exito');
            redirect('servicios');
        }

        if ($accion === 'actualizar') {
            $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);

            if ($id === false || $id === null || $id <= 0) {
                flash_set('Error: el ID del servicio no es válido.', 'error');
                redirect('servicios');
            }

            [$datos, $error] = servicio_validar_datos($_POST);

            if ($error !== null) {
                flash_set($error, 'error');
                redirect('servicios/editar', ['id' => $id]);
            }

            if (servicio_nombre_existe($datos['nombre'], $id)) {
                flash_set('Error: ya existe otro servicio con ese nombre.', 'error');
                redirect('servicios/editar', ['id' => $id]);
            }

            servicio_actualizar($id, $datos);
            flash_set('Servicio actualizado correctamente.', 'exito');
            redirect('servicios');
        }

        if ($accion === 'eliminar') {
            $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);

            if ($id === false || $id === null || $id <= 0) {
                flash_set('Error: el ID del servicio no es válido.', 'error');
            } elseif (servicio_eliminar($id) > 0) {
                flash_set('Servicio eliminado correctamente.', 'exito');
            } else {
                flash_set('No se encontró el servicio que intentas eliminar.', 'error');
            }

            redirect('servicios');
        }

        flash_set('Error: la acción solicitada no es válida.', 'error');
    } catch (PDOException $e) {
        error_log('Error en servicio_procesar: ' . $e->getMessage());
        $codigo = $e->getCode();

        if ($codigo === '23503') {
            flash_set('No se puede eliminar el servicio porque está asociado a citas registradas.', 'error');
        } elseif ($codigo === '23505') {
            flash_set('Error: ya existe un servicio con ese nombre.', 'error');
        } else {
            flash_set('No se pudo completar la operación. Verifica los datos y las restricciones de la base de datos.', 'error');
        }
    }

    redirect('servicios');
}

function servicio_icono_tratamiento(string $nombre, array $iconos): string
{
    $nombreNormalizado = mb_strtolower($nombre, 'UTF-8');

    foreach ($iconos as $palabra => $icono) {
        if (str_contains($nombreNormalizado, $palabra)) {
            return $icono;
        }
    }

    return 'fa-solid fa-tooth';
}

function servicio_catalogo(): void
{
    try {
        $servicios = servicio_listar_catalogo();
    } catch (PDOException $e) {
        error_log('Error al cargar el catálogo público de tratamientos: ' . $e->getMessage());
        http_response_code(500);
        exit('No se pudo cargar el catálogo de tratamientos. Inténtalo nuevamente más tarde.');
    }

    $iconosTratamiento = [
        'limpieza' => 'fa-solid fa-hand-sparkles',
        'blanqueamiento' => 'fa-solid fa-wand-magic-sparkles',
        'extraccion' => 'fa-solid fa-tooth',
        'endodoncia' => 'fa-solid fa-shield-halved',
        'ortodoncia' => 'fa-solid fa-teeth',
        'consulta' => 'fa-solid fa-user-doctor',
    ];

    render('servicio/catalogo', compact('servicios', 'iconosTratamiento'));
}
