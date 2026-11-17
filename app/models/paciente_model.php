<?php

declare(strict_types=1);

function paciente_buscar_por_id(int $id): ?array
{
    $stmt = db()->prepare(
        'SELECT id, nombre, cedula, telefono, email, fecha_nac, alergias, password_hash
         FROM paciente
         WHERE id = :id'
    );
    $stmt->execute([':id' => $id]);
    $paciente = $stmt->fetch();

    return $paciente ?: null;
}

function paciente_buscar_por_cedula(string $cedula): ?array
{
    $stmt = db()->prepare(
        'SELECT id, nombre, cedula, telefono, email, fecha_nac, alergias, password_hash
         FROM paciente
         WHERE cedula = :cedula
         LIMIT 1'
    );
    $stmt->execute([':cedula' => $cedula]);
    $paciente = $stmt->fetch();

    return $paciente ?: null;
}

function paciente_listar(): array
{
    return db()->query(
        'SELECT id, nombre, cedula, telefono, email, fecha_nac, alergias
         FROM paciente
         ORDER BY id DESC'
    )->fetchAll();
}

function paciente_crear(array $datos): int
{
    $stmt = db()->prepare(
        'INSERT INTO paciente (nombre, cedula, telefono, email, fecha_nac, alergias, password_hash)
         VALUES (:nombre, :cedula, :telefono, :email, :fecha_nac, :alergias, :password_hash)
         RETURNING id'
    );
    $stmt->execute([
        ':nombre' => $datos['nombre'],
        ':cedula' => $datos['cedula'] !== '' ? $datos['cedula'] : null,
        ':telefono' => $datos['telefono'],
        ':email' => $datos['email'] !== '' ? $datos['email'] : null,
        ':fecha_nac' => $datos['fecha_nac'] !== '' ? $datos['fecha_nac'] : null,
        ':alergias' => $datos['alergias'] !== '' ? $datos['alergias'] : null,
        ':password_hash' => $datos['password_hash'] ?? null,
    ]);

    return (int) $stmt->fetchColumn();
}

function paciente_actualizar(int $id, array $datos): bool
{
    $stmt = db()->prepare(
        'UPDATE paciente
         SET nombre = :nombre,
             cedula = :cedula,
             telefono = :telefono,
             email = :email,
             fecha_nac = :fecha_nac,
             alergias = :alergias
         WHERE id = :id'
    );

    return $stmt->execute([
        ':nombre' => $datos['nombre'],
        ':cedula' => $datos['cedula'],
        ':telefono' => $datos['telefono'],
        ':email' => $datos['email'] !== '' ? $datos['email'] : null,
        ':fecha_nac' => $datos['fecha_nac'] !== '' ? $datos['fecha_nac'] : null,
        ':alergias' => $datos['alergias'] !== '' ? $datos['alergias'] : null,
        ':id' => $id,
    ]);
}

function paciente_activar_cuenta(int $id, string $passwordHash, string $fechaNac, string $alergias): int
{
    $stmt = db()->prepare(
        'UPDATE paciente
         SET password_hash = :password_hash, fecha_nac = :fecha_nac, alergias = :alergias
         WHERE id = :id AND password_hash IS NULL'
    );
    $stmt->execute([
        ':password_hash' => $passwordHash,
        ':fecha_nac' => $fechaNac,
        ':alergias' => $alergias,
        ':id' => $id,
    ]);

    return $stmt->rowCount();
}

function paciente_cedula_duplicada(string $cedula, ?int $excluirId = null): bool
{
    $sql = 'SELECT id FROM paciente WHERE cedula = :cedula';
    $params = [':cedula' => $cedula];

    if ($excluirId !== null) {
        $sql .= ' AND id <> :id';
        $params[':id'] = $excluirId;
    }

    $stmt = db()->prepare($sql . ' LIMIT 1');
    $stmt->execute($params);

    return (bool) $stmt->fetch();
}

function paciente_contar(): int
{
    return (int) db()->query('SELECT COUNT(*) FROM paciente')->fetchColumn();
}

function paciente_citas(int $pacienteId): array
{
    $stmt = db()->prepare(
        "SELECT c.id, c.fecha, c.hora, c.estado::text AS estado,
                c.motivo, c.motivo_cancelacion,
                s.nombre AS servicio_nombre,
                o.nombre AS odontologo_nombre,
                o.especialidad AS odontologo_especialidad
         FROM cita c
         JOIN servicio s ON s.id = c.servicio_id
         LEFT JOIN odontologo o ON o.id = c.odontologo_id
         WHERE c.paciente_id = :paciente_id
         ORDER BY c.fecha DESC, c.hora DESC, c.id DESC"
    );
    $stmt->execute([':paciente_id' => $pacienteId]);

    return $stmt->fetchAll();
}
