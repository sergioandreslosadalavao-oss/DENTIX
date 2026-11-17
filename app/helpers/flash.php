<?php

declare(strict_types=1);

function flash_set(string $mensaje, string $tipo = 'exito'): void
{
    $_SESSION['mensaje'] = $mensaje;
    $_SESSION['tipo_mensaje'] = $tipo;
}

function flash_get(): array
{
    $mensaje = $_SESSION['mensaje'] ?? null;
    $tipo = $_SESSION['tipo_mensaje'] ?? 'exito';

    unset($_SESSION['mensaje'], $_SESSION['tipo_mensaje']);

    return [$mensaje, $tipo];
}

function error_set(string $mensaje): void
{
    $_SESSION['error'] = $mensaje;
}

function error_get(): ?string
{
    $error = $_SESSION['error'] ?? null;
    unset($_SESSION['error']);

    return $error;
}

function paciente_flash_set(string $mensaje): void
{
    $_SESSION['paciente_flash'] = $mensaje;
}

function paciente_flash_get(): string
{
    $mensaje = $_SESSION['paciente_flash'] ?? '';
    unset($_SESSION['paciente_flash']);

    return (string) $mensaje;
}
