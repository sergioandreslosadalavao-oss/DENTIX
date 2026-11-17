<?php

declare(strict_types=1);

function recepcionista_buscar_por_email(string $email): ?array
{
    $stmt = db()->prepare(
        'SELECT id, nombre, email, password_hash
         FROM recepcionista
         WHERE email = :email AND activo = TRUE
         LIMIT 1'
    );
    $stmt->execute([':email' => $email]);
    $recepcionista = $stmt->fetch();

    return $recepcionista ?: null;
}

function recepcionista_email_existe(string $email): bool
{
    $stmt = db()->prepare('SELECT id FROM recepcionista WHERE email = :email LIMIT 1');
    $stmt->execute([':email' => $email]);

    return (bool) $stmt->fetch();
}

function recepcionista_crear(string $nombre, string $telefono, string $email, string $passwordHash): bool
{
    $stmt = db()->prepare(
        'INSERT INTO recepcionista (nombre, telefono, email, password_hash)
         VALUES (:nombre, :telefono, :email, :password_hash)'
    );

    return $stmt->execute([
        ':nombre' => $nombre,
        ':telefono' => $telefono,
        ':email' => $email,
        ':password_hash' => $passwordHash,
    ]);
}
