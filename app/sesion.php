<?php

// app/sesion.php — Guardas de sesión: autenticación y autorización por rol.
// CONCEPTO: session_start() solo inicia una sesión ANÓNIMA. Aquí está la capa
// que verifica que el usuario ESTÁ autenticado y que su sesión sigue vigente
// en la base de datos (no confiamos en datos viejos de la sesión).

session_start();

require_once __DIR__ . "/../config/database.php";

// Mensaje de una sola vista (flash message) compartido por todas las guardas
function sesion_denegar(string $destino, ?string $mensaje = null): void
{
    if ($mensaje !== null) {
        $_SESSION["error"] = $mensaje;
    }
    header("Location: " . $destino);
    exit;
}

// Requiere sesión de RECEPCIONISTA activa. Revalida contra la BD por si en la
// vida de la sesión alguien desactivó la cuenta (usurpación de sesión vieja).
function sesion_requiere_recepcionista(string $destino, ?string $mensaje = null): int
{
    $id = filter_var($_SESSION["recepcionista_id"] ?? null, FILTER_VALIDATE_INT);

    if ($id === false || $id === null) {
        sesion_denegar(
            $destino,
            $mensaje ?? "Necesitas iniciar sesión de recepción para acceder a este módulo."
        );
    }

    $consulta = $GLOBALS["conexion"]->prepare(
        "SELECT 1 FROM recepcionista WHERE id = :id AND activo = TRUE"
    );
    $consulta->execute([":id" => $id]);

    if (!$consulta->fetch()) {
        session_unset();
        session_destroy();
        session_start();
        sesion_denegar($destino, "Tu sesión ya no es válida. Inicia sesión nuevamente.");
    }

    return (int) $id;
}

// Requiere sesión de ODONTÓLOGO LÍDER activo (RN-07).
// Revalida el cargo contra la BD: la sesión NO es la fuente de verdad del cargo.
function sesion_requiere_odontologo_lider(string $destino, ?string $mensaje = null): int
{
    $id = filter_var($_SESSION["odontologo_id"] ?? null, FILTER_VALIDATE_INT);

    if ($id === false || $id === null) {
        sesion_denegar(
            $destino,
            $mensaje ?? "Solo el odontólogo líder puede gestionar odontólogos. Inicia sesión."
        );
    }

    $consulta = $GLOBALS["conexion"]->prepare(
        "SELECT 1 FROM odontologo WHERE id = :id AND activo = TRUE AND cargo = 'lider'"
    );
    $consulta->execute([":id" => $id]);

    if (!$consulta->fetch()) {
        sesion_denegar(
            $destino,
            "Solo el odontólogo líder activo puede registrar o modificar odontólogos (RN-07)."
        );
    }

    return (int) $id;
}

// Requiere CUALQUIER usuario autenticado (recepcionista u odontólogo líder).
function sesion_requiere_autenticado(string $destino, ?string $mensaje = null): void
{
    $esRecepcionista = filter_var($_SESSION["recepcionista_id"] ?? null, FILTER_VALIDATE_INT) !== false;
    $esOdontologo = filter_var($_SESSION["odontologo_id"] ?? null, FILTER_VALIDATE_INT) !== false;

    if (!$esRecepcionista && !$esOdontologo) {
        sesion_denegar($destino, $mensaje ?? "Necesitas iniciar sesión para acceder a este módulo.");
    }
}
