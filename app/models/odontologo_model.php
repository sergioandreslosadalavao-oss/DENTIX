<?php

declare(strict_types=1);

function odontologo_generar_slug(string $nombre): string
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

function odontologo_buscar_por_id(int $id): ?array
{
    $stmt = db()->prepare(
        'SELECT id, nombre, especialidad, consultorio, slug_publico, email, activo
         FROM odontologo
         WHERE id = :id'
    );
    $stmt->execute([':id' => $id]);
    $odontologo = $stmt->fetch();

    return $odontologo ?: null;
}

function odontologo_buscar_por_email(string $email): ?array
{
    $stmt = db()->prepare(
        'SELECT id, nombre, especialidad, consultorio, password_hash
         FROM odontologo
         WHERE email = :email AND activo = TRUE
         LIMIT 1'
    );
    $stmt->execute([':email' => $email]);
    $odontologo = $stmt->fetch();

    return $odontologo ?: null;
}

function odontologo_listar_activos(): array
{
    return db()->query(
        'SELECT id, nombre, especialidad
         FROM odontologo
         WHERE activo IS TRUE
         ORDER BY nombre'
    )->fetchAll();
}

function odontologo_listar_con_citas(): array
{
    return db()->query(
        "SELECT o.*,
                COUNT(c.id) AS total_citas_activas
         FROM odontologo o
         LEFT JOIN cita c ON c.odontologo_id = o.id AND c.estado = 'confirmada'
         GROUP BY o.id
         ORDER BY o.id ASC"
    )->fetchAll();
}

function odontologo_slug_existe(string $slug, ?int $excluirId = null): bool
{
    $sql = 'SELECT id FROM odontologo WHERE slug_publico = :slug';
    $params = [':slug' => $slug];

    if ($excluirId !== null) {
        $sql .= ' AND id <> :id';
        $params[':id'] = $excluirId;
    }

    $stmt = db()->prepare($sql . ' LIMIT 1');
    $stmt->execute($params);

    return (bool) $stmt->fetch();
}

function odontologo_email_existe(string $email, ?int $excluirId = null): bool
{
    $sql = 'SELECT id FROM odontologo WHERE lower(email) = lower(:email)';
    $params = [':email' => $email];

    if ($excluirId !== null) {
        $sql .= ' AND id <> :id';
        $params[':id'] = $excluirId;
    }

    $stmt = db()->prepare($sql . ' LIMIT 1');
    $stmt->execute($params);

    return (bool) $stmt->fetch();
}

function odontologo_guardar(array $datos): bool
{
    $stmt = db()->prepare(
        'INSERT INTO odontologo
            (nombre, especialidad, consultorio, slug_publico, email, password_hash, activo)
         VALUES
            (:nombre, :especialidad, :consultorio, :slug_publico, :email, :password_hash, TRUE)'
    );

    return $stmt->execute([
        ':nombre' => $datos['nombre'],
        ':especialidad' => $datos['especialidad'],
        ':consultorio' => $datos['consultorio'] !== '' ? $datos['consultorio'] : null,
        ':slug_publico' => $datos['slug_publico'],
        ':email' => $datos['email'],
        ':password_hash' => $datos['password_hash'],
    ]);
}

function odontologo_actualizar(int $id, array $datos): bool
{
    $stmt = db()->prepare(
        'UPDATE odontologo
         SET nombre = :nombre,
             especialidad = :especialidad,
             consultorio = :consultorio,
             slug_publico = :slug_publico,
             email = :email,
             activo = :activo
         WHERE id = :id'
    );

    return $stmt->execute([
        ':nombre' => $datos['nombre'],
        ':especialidad' => $datos['especialidad'],
        ':consultorio' => $datos['consultorio'] !== '' ? $datos['consultorio'] : null,
        ':slug_publico' => $datos['slug_publico'],
        ':email' => $datos['email'],
        ':activo' => $datos['activo'] ? 'true' : 'false',
        ':id' => $id,
    ]);
}

function odontologo_eliminar(int $id): int
{
    $stmt = db()->prepare('DELETE FROM odontologo WHERE id = :id');
    $stmt->execute([':id' => $id]);

    return $stmt->rowCount();
}

function odontologo_actualizar_ultimo_acceso(int $id): void
{
    $stmt = db()->prepare('UPDATE odontologo SET ultimo_acceso = CURRENT_TIMESTAMP WHERE id = :id');
    $stmt->execute([':id' => $id]);
}

function odontologo_citas_activas(int $odontologoId): array
{
    $stmt = db()->prepare(
        "SELECT c.id, c.fecha, c.hora, c.motivo,
                p.nombre AS paciente_nombre, p.cedula AS paciente_cedula,
                p.telefono AS paciente_telefono, p.alergias AS paciente_alergias,
                s.nombre AS servicio_nombre, s.duracion_min
         FROM cita c
         INNER JOIN paciente p ON p.id = c.paciente_id
         INNER JOIN servicio s ON s.id = c.servicio_id
         WHERE c.odontologo_id = :odontologo_id
           AND c.estado = 'confirmada'
         ORDER BY c.fecha ASC, c.hora ASC"
    );
    $stmt->execute([':odontologo_id' => $odontologoId]);

    return $stmt->fetchAll();
}

function odontologo_citas_finalizadas(int $odontologoId): array
{
    $stmt = db()->prepare(
        "SELECT c.id, c.fecha, c.hora,
                p.nombre AS paciente_nombre, p.cedula AS paciente_cedula,
                p.telefono AS paciente_telefono, p.alergias AS paciente_alergias,
                s.nombre AS servicio_nombre, s.duracion_min,
                dc.detalle, dc.procedimiento, dc.nota_clinica,
                dc.fecha_salida, dc.hora_salida, dc.creado_en AS detalle_fecha
         FROM cita c
         INNER JOIN paciente p ON p.id = c.paciente_id
         INNER JOIN servicio s ON s.id = c.servicio_id
         LEFT JOIN detalle_cita dc ON dc.cita_id = c.id
         WHERE c.odontologo_id = :odontologo_id
           AND c.estado = 'completada'
         ORDER BY COALESCE(dc.creado_en, c.creado_en) DESC, c.fecha DESC, c.hora DESC"
    );
    $stmt->execute([':odontologo_id' => $odontologoId]);

    return $stmt->fetchAll();
}

function odontologo_finalizar_cita(
    int $citaId,
    int $odontologoId,
    string $detalle,
    string $procedimiento,
    string $notaClinica,
    string $fechaSalida,
    string $horaSalida
): bool {
    $conexion = db();
    $conexion->beginTransaction();

    try {
        $stmt = $conexion->prepare(
            "UPDATE cita
             SET estado = 'completada'
             WHERE id = :cita_id
               AND odontologo_id = :odontologo_id
               AND estado = 'confirmada'
             RETURNING servicio_id"
        );
        $stmt->execute([
            ':cita_id' => $citaId,
            ':odontologo_id' => $odontologoId,
        ]);

        $servicioId = $stmt->fetchColumn();

        if (!$servicioId) {
            $conexion->rollBack();

            return false;
        }

        $stmtDetalle = $conexion->prepare(
            'INSERT INTO detalle_cita
                (cita_id, servicio_id, detalle, procedimiento, nota_clinica, fecha_salida, hora_salida)
             VALUES
                (:cita_id, :servicio_id, :detalle, :procedimiento, :nota_clinica, :fecha_salida, :hora_salida)'
        );
        $stmtDetalle->execute([
            ':cita_id' => $citaId,
            ':servicio_id' => $servicioId,
            ':detalle' => $detalle,
            ':procedimiento' => $procedimiento,
            ':nota_clinica' => $notaClinica !== '' ? $notaClinica : 'Sin novedades o problemáticas reportadas.',
            ':fecha_salida' => $fechaSalida,
            ':hora_salida' => $horaSalida,
        ]);

        $conexion->commit();

        return true;
    } catch (PDOException $e) {
        if ($conexion->inTransaction()) {
            $conexion->rollBack();
        }

        throw $e;
    }
}
