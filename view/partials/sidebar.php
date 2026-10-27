<?php

require_once __DIR__ . '/bootstrap.php';

$activeNav = $activeNav ?? '';

?>
<aside class="sidebar" id="sidebar">
    <div class="sidebar__top">
        <a class="brand" href="<?= sco_href('index.php') ?>" aria-label="DENTIX, inicio">
            <span class="brand__mark" aria-hidden="true">
                <svg viewBox="0 0 448 512">
                    <path d="M443.98 96.01c0-56.43-45.53-96.01-112.9-96.01-44.65 0-60.15 25-92.08 25-31.03 0-48.88-25-92.08-25C77.55 0 32 39.59 32 96.01c0 39.02 16.93 70.15 38.33 111.1 15.68 30.02 25.75 66.62 31.28 105.8C108.7 366.2 118.1 512 156.5 512c24.41 0 31.51-21.11 38.51-49.11 5.95-23.84 12.06-48.39 29.09-48.39 17.03 0 23.14 24.55 29.09 48.39 7 28 14.1 49.11 38.51 49.11 38.4 0 47.8-145.8 54.89-198.1 5.53-39.2 15.6-75.79 31.28-105.8 21.4-40.92 38.33-72.05 38.33-111.09z"/>
                </svg>
            </span>
            <span class="brand__text">
                DENTIX
                <small>Gestión odontológica</small>
            </span>
        </a>

        <button class="sidebar__close" id="sidebar-close" type="button" aria-label="Cerrar menú">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg>
        </button>
    </div>

    <nav class="sidebar__nav" aria-label="Navegación del panel">
        <span class="sidebar__label">General</span>

        <a class="side-link" href="<?= sco_href('index.php') ?>">
            <span class="side-link__icon">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m3 10 9-7 9 7v10a1 1 0 0 1-1 1h-5v-7H9v7H4a1 1 0 0 1-1-1z"/></svg>
            </span>
            <span>Inicio</span>
        </a>

        <span class="sidebar__label">Gestión</span>

        <a class="side-link<?= sco_active('pacientes', $activeNav) ?>" href="<?= sco_href('view/paciente/lista_pacientes.php') ?>">
            <span class="side-link__icon">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            </span>
            <span>Pacientes</span>
        </a>

        <a class="side-link<?= sco_active('odontologos', $activeNav) ?>" href="<?= sco_href('view/odontologo/lista_odontologos.php') ?>">
            <span class="side-link__icon">
                <svg viewBox="0 0 448 512" aria-hidden="true"><path d="M443.98 96.01c0-56.43-45.53-96.01-112.9-96.01-44.65 0-60.15 25-92.08 25-31.03 0-48.88-25-92.08-25C77.55 0 32 39.59 32 96.01c0 39.02 16.93 70.15 38.33 111.1 15.68 30.02 25.75 66.62 31.28 105.8C108.7 366.2 118.1 512 156.5 512c24.41 0 31.51-21.11 38.51-49.11 5.95-23.84 12.06-48.39 29.09-48.39 17.03 0 23.14 24.55 29.09 48.39 7 28 14.1 49.11 38.51 49.11 38.4 0 47.8-145.8 54.89-198.1 5.53-39.2 15.6-75.79 31.28-105.8 21.4-40.92 38.33-72.05 38.33-111.09z"/></svg>
            </span>
            <span>Odontólogos</span>
        </a>

        <a class="side-link<?= sco_active('servicios', $activeNav) ?>" href="<?= sco_href('view/servicio/lista_servicios.php') ?>">
            <span class="side-link__icon">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2"/><rect x="9" y="3" width="6" height="4" rx="1"/><path d="M9 12h6M9 16h4"/></svg>
            </span>
            <span>Servicios</span>
        </a>
    </nav>

    <div class="sidebar__footer">
        <div class="sidebar__note">
            <span class="sidebar__note-icon">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3v3M12 18v3M3 12h3M18 12h3M5.6 5.6l2.1 2.1M16.3 16.3l2.1 2.1M18.4 5.6l-2.1 2.1M7.7 16.3l-2.1 2.1"/></svg>
            </span>
            <div>
                <strong>Todo en orden</strong>
                <span>Gestiona tu consultorio con calma.</span>
            </div>
        </div>

        <a class="sidebar__site" href="<?= sco_href('index.php') ?>">
            Ver sitio público
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
        </a>
    </div>
</aside>

<div class="sidebar-backdrop" id="sidebar-backdrop" hidden></div>
