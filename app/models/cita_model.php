<?php

declare(strict_types=1);

function cita_traducir_error(PDOException $e, string $fallback): string
{
    $raw = $e->getMessage();
    $code = $e->getCode();

    if ($code === '23505') {
        return 'El odontólogo ya tiene una cita en esa misma fecha y hora.';
    }
    if (str_contains($raw, 'El paciente ya cuenta con una cita')) {
        return 'El paciente ya tiene una cita registrada para ese mismo día (máximo 1 cita por día).';
    }
    if (str_contains($raw, 'Deben pasar al menos 4 días')) {
        return 'Deben pasar al menos 4 días completos entre citas para el mismo paciente.';
    }
    if (str_contains($raw, 'El odontólogo ya tiene una cita asignada')) {
        return 'El odontólogo seleccionado ya tiene una cita asignada ese día (máximo 1 cita por odontólogo al día).';
    }
    if (str_contains($raw, 'ck_cita_horario')) {
        return 'El horario debe estar entre 08:00 y 19:30 en intervalos de 30 minutos.';
    }
    if (str_contains($raw, 'ck_cita_cancelacion')) {
        return 'Debes ingresar un motivo de cancelación para cancelar la cita.';
    }

    return $fallback;
}

function cita_buscar_por_id(int $id): ?array
{
    $stmt = db()->prepare('SELECT * FROM cita WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $cita = $stmt->fetch();

    return $cita ?: null;
}

function cita_listar_pendientes(): array
{
    return db()->query(
        "SELECT c.id, c.fecha, c.hora, p.nombre AS paciente_nombre, p.cedula,
                s.nombre AS servicio_nombre, o.id AS odontologo_id
         FROM cita c
         JOIN paciente p ON p.id = c.paciente_id
         JOIN servicio s ON s.id = c.servicio_id
         LEFT JOIN odontologo o ON o.id = c.odontologo_id
         WHERE c.estado = 'pendiente'
         ORDER BY c.fecha, c.hora, c.id"
    )->fetchAll();
}

function cita_listar_confirmadas(): array
{
    return db()->query(
        "SELECT c.id, c.fecha, c.hora, p.nombre AS paciente_nombre, p.cedula,
                s.nombre AS servicio_nombre, o.nombre AS odontologo_nombre,
                o.especialidad AS odontologo_especialidad
         FROM cita c
         JOIN paciente p ON p.id = c.paciente_id
         JOIN servicio s ON s.id = c.servicio_id
         JOIN odontologo o ON o.id = c.odontologo_id
         WHERE c.estado = 'confirmada' AND c.odontologo_id IS NOT NULL
         ORDER BY c.fecha DESC, c.hora DESC, c.id DESC"
    )->fetchAll();
}

function cita_guardar(array $datos): bool
{
    $stmt = db()->prepare(
        'INSERT INTO cita
            (paciente_id, odontologo_id, servicio_id, recepcionista_id, fecha, hora, motivo, estado)
         VALUES
            (:paciente_id, :odontologo_id, :servicio_id, :recepcionista_id, :fecha, :hora, :motivo, :estado)'
    );

    return $stmt->execute([
        ':paciente_id' => $datos['paciente_id'],
        ':odontologo_id' => $datos['odontologo_id'],
        ':servicio_id' => $datos['servicio_id'],
        ':recepcionista_id' => $datos['recepcionista_id'] ?? null,
        ':fecha' => $datos['fecha'],
        ':hora' => $datos['hora'],
        ':motivo' => $datos['motivo'] !== '' ? substr($datos['motivo'], 0, 200) : null,
        ':estado' => $datos['estado'],
    ]);
}

function cita_actualizar(int $id, array $datos): bool
{
    $stmt = db()->prepare(
        'UPDATE cita
         SET paciente_id = :paciente_id,
             odontologo_id = :odontologo_id,
             servicio_id = :servicio_id,
             recepcionista_id = :recepcionista_id,
             fecha = :fecha,
             hora = :hora,
             estado = :estado,
             motivo = :motivo,
             motivo_cancelacion = :motivo_cancelacion
         WHERE id = :id'
    );

    return $stmt->execute([
        ':paciente_id' => $datos['paciente_id'],
        ':odontologo_id' => $datos['odontologo_id'],
        ':servicio_id' => $datos['servicio_id'],
        ':recepcionista_id' => $datos['recepcionista_id'],
        ':fecha' => $datos['fecha'],
        ':hora' => $datos['hora'],
        ':estado' => $datos['estado'],
        ':motivo' => $datos['motivo'] !== '' ? substr($datos['motivo'], 0, 200) : null,
        ':motivo_cancelacion' => $datos['estado'] === 'cancelada' ? substr($datos['motivo_cancelacion'], 0, 200) : null,
        ':id' => $id,
    ]);
}

function cita_cancelar(int $id, int $recepcionistaId, string $motivo): bool
{
    $stmt = db()->prepare(
        "UPDATE cita
         SET estado = 'cancelada',
             motivo_cancelacion = :motivo_cancelacion,
             recepcionista_id = :recepcionista_id
         WHERE id = :id
           AND estado IN ('pendiente', 'confirmada')
         RETURNING id"
    );
    $stmt->execute([
        ':motivo_cancelacion' => substr($motivo, 0, 200),
        ':recepcionista_id' => $recepcionistaId,
        ':id' => $id,
    ]);

    return (bool) $stmt->fetchColumn();
}

function cita_confirmar(int $citaId, int $odontologoId, int $recepcionistaId): bool
{
    $stmt = db()->prepare(
        "UPDATE cita
         SET odontologo_id = :odontologo_id,
             estado = 'confirmada',
             recepcionista_id = :recepcionista_id
         WHERE id = :id AND estado = 'pendiente'
         RETURNING id"
    );
    $stmt->execute([
        ':odontologo_id' => $odontologoId,
        ':recepcionista_id' => $recepcionistaId,
        ':id' => $citaId,
    ]);

    return (bool) $stmt->fetchColumn();
}

function cita_odontologo_tiene_conflicto(int $odontologoId, string $fecha, int $excluirCitaId): bool
{
    $stmt = db()->prepare(
        "SELECT id FROM cita
         WHERE odontologo_id = :odontologo_id
           AND fecha = :fecha
           AND id <> :id
           AND estado IN ('confirmada', 'completada')
         LIMIT 1"
    );
    $stmt->execute([
        ':odontologo_id' => $odontologoId,
        ':fecha' => $fecha,
        ':id' => $excluirCitaId,
    ]);

    return (bool) $stmt->fetchColumn();
}

function cita_cercana_paciente(int $pacienteId, string $fecha): ?array
{
    $stmt = db()->prepare(
        "SELECT fecha
         FROM cita
         WHERE paciente_id = :paciente_id
           AND estado IN ('pendiente', 'confirmada', 'completada')
           AND ABS(fecha - :fecha::date) < 5
         LIMIT 1"
    );
    $stmt->execute([
        ':paciente_id' => $pacienteId,
        ':fecha' => $fecha,
    ]);
    $cita = $stmt->fetch();

    return $cita ?: null;
}
