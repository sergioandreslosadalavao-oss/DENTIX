<?php

require_once __DIR__ . '/bootstrap.php';

$pageTitle = $pageTitle ?? 'Panel';
$pageSection = $pageSection ?? $pageTitle;
$pageAccent = $pageAccent ?? 'pacientes';
$activeNav = $activeNav ?? '';

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="DENTIX, Sistema de Citas Odontológicas.">
    <title><?= sco_e($pageTitle) ?> | DENTIX</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Sora:wght@600;700;800&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="<?= sco_asset_href('css/admin.css') ?>">
</head>
<body class="admin-body" data-accent="<?= sco_e($pageAccent) ?>">

    <div class="page-progress" aria-hidden="true">
        <span class="page-progress__bar"></span>
    </div>

    <div class="app-shell">

        <?php require __DIR__ . '/sidebar.php'; ?>

        <div class="app-main">
            <header class="topbar">
                <button
                    class="menu-btn"
                    id="menu-toggle"
                    type="button"
                    aria-label="Abrir menú"
                    aria-controls="sidebar"
                    aria-expanded="false"
                >
                    <span></span>
                    <span></span>
                    <span></span>
                </button>

                <nav class="topbar__crumbs" aria-label="Miga de pan">
                    <a href="<?= sco_href('index.php') ?>">DENTIX</a>
                    <span aria-hidden="true">/</span>
                    <strong><?= sco_e($pageSection) ?></strong>
                </nav>

                <div class="topbar__actions">
                    <span class="topbar__date"><?= date('d/m/Y') ?></span>
                    <a class="icon-btn" href="<?= sco_href('index.php') ?>" aria-label="Volver al inicio" title="Volver al inicio">
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path d="m3 10 9-7 9 7v10a1 1 0 0 1-1 1h-5v-7H9v7H4a1 1 0 0 1-1-1z"/>
                        </svg>
                    </a>
                </div>
            </header>

            <main class="page">
