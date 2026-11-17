<?php

declare(strict_types=1);

require_once APP_PATH . '/models/reporte_model.php';

function reporte_index(): void
{
    requerir_recepcionista('login-recepcionista');

    $fechaDesde = trim($_GET['desde'] ?? '');
    $fechaHasta = trim($_GET['hasta'] ?? '');

    $validarFecha = static function (string $f): bool {
        if ($f === '') {
            return false;
        }

        $dt = DateTimeImmutable::createFromFormat('!Y-m-d', $f);

        return $dt !== false && $dt->format('Y-m-d') === $f;
    };

    if (!$validarFecha($fechaDesde)) {
        $fechaDesde = '';
    }
    if (!$validarFecha($fechaHasta)) {
        $fechaHasta = '';
    }
    if ($fechaDesde !== '' && $fechaHasta !== '' && $fechaDesde > $fechaHasta) {
        [$fechaDesde, $fechaHasta] = [$fechaHasta, $fechaDesde];
    }

    $error = '';

    try {
        $datos = reporte_obtener_datos($fechaDesde, $fechaHasta);
    } catch (PDOException $e) {
        error_log('Error al generar reportes en recepción: ' . $e->getMessage());
        $datos = [
            'resumen' => [
                'total_citas' => 0,
                'pendientes' => 0,
                'confirmadas' => 0,
                'completadas' => 0,
                'canceladas' => 0,
                'ingresos_estimados' => 0,
            ],
            'totalPacientes' => 0,
            'totalOdontologosActivos' => 0,
            'topPacientes' => [],
            'topServicios' => [],
            'topOdontologos' => [],
            'horariosPico' => [],
        ];
        $error = 'No se pudieron cargar los reportes en este momento.';
    }

    extract($datos);

    $liderPaciente = ($topPacientes !== [] && (int) $topPacientes[0]['total_citas'] > 0) ? $topPacientes[0] : null;
    $liderServicio = ($topServicios !== [] && (int) $topServicios[0]['total_solicitudes'] > 0) ? $topServicios[0] : null;
    $liderOdontologo = ($topOdontologos !== [] && (int) $topOdontologos[0]['total_citas'] > 0) ? $topOdontologos[0] : null;

    $maxCitasPaciente = $liderPaciente ? max(1, (int) $liderPaciente['total_citas']) : 1;
    $totalCitasServicios = array_sum(array_map(static fn($r) => (int) $r['total_solicitudes'], $topServicios));
    $maxCitasOdontologo = $liderOdontologo ? max(1, (int) $liderOdontologo['total_citas']) : 1;
    $maxHorario = $horariosPico !== [] ? max(1, (int) $horariosPico[0]['total']) : 1;

    render('recepcionista/reportes', compact(
        'fechaDesde',
        'fechaHasta',
        'resumen',
        'totalPacientes',
        'totalOdontologosActivos',
        'topPacientes',
        'topServicios',
        'topOdontologos',
        'horariosPico',
        'error',
        'liderPaciente',
        'liderServicio',
        'liderOdontologo',
        'maxCitasPaciente',
        'totalCitasServicios',
        'maxCitasOdontologo',
        'maxHorario'
    ));
}
