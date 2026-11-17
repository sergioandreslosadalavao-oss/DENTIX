<?php

declare(strict_types=1);

function servicio_listar(): array
{
    return db()->query('SELECT * FROM servicio ORDER BY id DESC')->fetchAll();
}

function servicio_listar_ordenado(): array
{
    return db()->query('SELECT id, nombre FROM servicio ORDER BY nombre')->fetchAll();
}

function servicio_listar_catalogo(): array
{
    return db()->query(
        'SELECT id, nombre, descripcion, duracion_min, precio
         FROM servicio
         ORDER BY nombre'
    )->fetchAll();
}

function servicio_buscar_por_id(int $id): ?array
{
    $stmt = db()->prepare(
        'SELECT id, nombre, descripcion, duracion_min, precio
         FROM servicio
         WHERE id = :id'
    );
    $stmt->execute([':id' => $id]);
    $servicio = $stmt->fetch();

    return $servicio ?: null;
}

function servicio_existe(int $id): bool
{
    $stmt = db()->prepare('SELECT id FROM servicio WHERE id = :id');
    $stmt->execute([':id' => $id]);

    return (bool) $stmt->fetchColumn();
}

function servicio_nombre_existe(string $nombre, ?int $excluirId = null): bool
{
    $sql = 'SELECT id FROM servicio WHERE lower(nombre) = lower(:nombre)';
    $params = [':nombre' => $nombre];

    if ($excluirId !== null) {
        $sql .= ' AND id <> :id';
        $params[':id'] = $excluirId;
    }

    $stmt = db()->prepare($sql . ' LIMIT 1');
    $stmt->execute($params);

    return (bool) $stmt->fetch();
}

function servicio_guardar(array $datos): bool
{
    $stmt = db()->prepare(
        'INSERT INTO servicio (nombre, descripcion, duracion_min, precio)
         VALUES (:nombre, :descripcion, :duracion_min, :precio)'
    );

    return $stmt->execute([
        ':nombre' => $datos['nombre'],
        ':descripcion' => $datos['descripcion'] !== '' ? $datos['descripcion'] : null,
        ':duracion_min' => $datos['duracion_min'],
        ':precio' => $datos['precio'],
    ]);
}

function servicio_actualizar(int $id, array $datos): bool
{
    $stmt = db()->prepare(
        'UPDATE servicio
         SET nombre = :nombre,
             descripcion = :descripcion,
             duracion_min = :duracion_min,
             precio = :precio
         WHERE id = :id'
    );

    return $stmt->execute([
        ':nombre' => $datos['nombre'],
        ':descripcion' => $datos['descripcion'],
        ':duracion_min' => $datos['duracion_min'],
        ':precio' => $datos['precio'],
        ':id' => $id,
    ]);
}

function servicio_eliminar(int $id): int
{
    $stmt = db()->prepare('DELETE FROM servicio WHERE id = :id');
    $stmt->execute([':id' => $id]);

    return $stmt->rowCount();
}
