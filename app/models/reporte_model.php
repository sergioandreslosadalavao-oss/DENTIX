<?php

declare(strict_types=1);

function reporte_obtener_datos(string $fechaDesde, string $fechaHasta): array
{
    $condicionesCita = [];
    $paramsCita = [];

    if ($fechaDesde !== '') {
        $condicionesCita[] = 'c.fecha >= :desde';
        $paramsCita[':desde'] = $fechaDesde;
    }
    if ($fechaHasta !== '') {
        $condicionesCita[] = 'c.fecha <= :hasta';
        $paramsCita[':hasta'] = $fechaHasta;
    }

    $whereCita = $condicionesCita ? ('WHERE ' . implode(' AND ', $condicionesCita)) : '';
    $andCita = $condicionesCita ? ('AND ' . implode(' AND ', $condicionesCita)) : '';

    $resumen = [
        'total_citas' => 0,
        'pendientes' => 0,
        'confirmadas' => 0,
        'completadas' => 0,
        'canceladas' => 0,
        'ingresos_estimados' => 0,
    ];

    $stmtResumen = db()->prepare(
        "SELECT
            COUNT(c.id) AS total_citas,
            COUNT(c.id) FILTER (WHERE c.estado = 'pendiente') AS pendientes,
            COUNT(c.id) FILTER (WHERE c.estado = 'confirmada') AS confirmadas,
            COUNT(c.id) FILTER (WHERE c.estado = 'completada') AS completadas,
            COUNT(c.id) FILTER (WHERE c.estado IN ('cancelada', 'no_show')) AS canceladas,
            COALESCE(SUM(s.precio) FILTER (WHERE c.estado IN ('confirmada', 'completada')), 0) AS ingresos_estimados
         FROM cita c
         INNER JOIN servicio s ON s.id = c.servicio_id
         {$whereCita}"
    );
    $stmtResumen->execute($paramsCita);
    $resumenDb = $stmtResumen->fetch();
    if ($resumenDb) {
        $resumen = $resumenDb;
    }

    $totalPacientes = (int) db()->query('SELECT COUNT(*) FROM paciente')->fetchColumn();
    $totalOdontologosActivos = (int) db()->query('SELECT COUNT(*) FROM odontologo WHERE activo IS TRUE')->fetchColumn();

    $stmtPacientes = db()->prepare(
        "SELECT p.id, p.nombre, p.cedula, p.telefono,
                COUNT(c.id) AS total_citas,
                COUNT(c.id) FILTER (WHERE c.estado = 'completada') AS completadas,
                COUNT(c.id) FILTER (WHERE c.estado = 'confirmada') AS confirmadas,
                COUNT(c.id) FILTER (WHERE c.estado = 'pendiente') AS pendientes,
                COUNT(c.id) FILTER (WHERE c.estado IN ('cancelada', 'no_show')) AS canceladas,
                MAX(c.fecha) AS ultima_cita
         FROM paciente p
         INNER JOIN cita c ON c.paciente_id = p.id
         {$whereCita}
         GROUP BY p.id, p.nombre, p.cedula, p.telefono
         ORDER BY total_citas DESC, completadas DESC, p.nombre ASC"
    );
    $stmtPacientes->execute($paramsCita);
    $topPacientes = $stmtPacientes->fetchAll();

    $stmtServicios = db()->prepare(
        "SELECT s.id, s.nombre, s.duracion_min, s.precio,
                COUNT(c.id) AS total_solicitudes,
                COUNT(c.id) FILTER (WHERE c.estado = 'completada') AS completadas,
                COUNT(c.id) FILTER (WHERE c.estado = 'confirmada') AS confirmadas,
                COUNT(c.id) FILTER (WHERE c.estado = 'pendiente') AS pendientes,
                COALESCE(SUM(s.precio) FILTER (WHERE c.estado IN ('confirmada', 'completada')), 0) AS ingresos_estimados
         FROM servicio s
         LEFT JOIN cita c ON c.servicio_id = s.id {$andCita}
         GROUP BY s.id, s.nombre, s.duracion_min, s.precio
         ORDER BY total_solicitudes DESC, completadas DESC, s.nombre ASC"
    );
    $stmtServicios->execute($paramsCita);
    $topServicios = $stmtServicios->fetchAll();

    $stmtOdontologos = db()->prepare(
        "SELECT o.id, o.nombre, o.especialidad, o.consultorio, o.activo,
                COUNT(c.id) AS total_citas,
                COUNT(c.id) FILTER (WHERE c.estado = 'completada') AS finalizadas,
                COUNT(c.id) FILTER (WHERE c.estado = 'confirmada') AS confirmadas,
                MAX(c.fecha) AS ultima_fecha
         FROM odontologo o
         LEFT JOIN cita c ON c.odontologo_id = o.id AND c.estado <> 'cancelada' {$andCita}
         GROUP BY o.id, o.nombre, o.especialidad, o.consultorio, o.activo
         ORDER BY total_citas DESC, finalizadas DESC, o.nombre ASC"
    );
    $stmtOdontologos->execute($paramsCita);
    $topOdontologos = $stmtOdontologos->fetchAll();

    $stmtHorarios = db()->prepare(
        "SELECT to_char(c.hora, 'HH24:MI') AS franja,
                COUNT(c.id) AS total
         FROM cita c
         {$whereCita}
         GROUP BY franja
         ORDER BY total DESC, franja ASC
         LIMIT 6"
    );
    $stmtHorarios->execute($paramsCita);
    $horariosPico = $stmtHorarios->fetchAll();

    return [
        'resumen' => $resumen,
        'totalPacientes' => $totalPacientes,
        'totalOdontologosActivos' => $totalOdontologosActivos,
        'topPacientes' => $topPacientes,
        'topServicios' => $topServicios,
        'topOdontologos' => $topOdontologos,
        'horariosPico' => $horariosPico,
    ];
}
