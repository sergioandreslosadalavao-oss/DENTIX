<?php

session_start();

require_once __DIR__ . "/../../config/database.php";

$mensaje = $_SESSION["mensaje"] ?? null;
$tipo_mensaje = $_SESSION["tipo_mensaje"] ?? "exito";

unset($_SESSION["mensaje"], $_SESSION["tipo_mensaje"]);

$busqueda = trim($_GET["nombre"] ?? "");

try {

    if ($busqueda !== "") {

        $sql = "SELECT id, nombre, especialidad, consultorio, slug_publico
                FROM odontologo
                WHERE nombre ILIKE :nombre OR especialidad ILIKE :nombre
                ORDER BY id ASC";

        $consulta = $conexion->prepare($sql);
        $consulta->execute([":nombre" => "%" . $busqueda . "%"]);

    } else {

        $sql = "SELECT id, nombre, especialidad, consultorio, slug_publico
                FROM odontologo
                ORDER BY id ASC";

        $consulta = $conexion->query($sql);
    }

    $odontologos = $consulta->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {

    error_log("Error al listar odontólogos: " . $e->getMessage());

    $odontologos = [];
    $mensaje = "No se pudo cargar el listado de odontólogos.";
    $tipo_mensaje = "error";
}

$totalOdontologos = count($odontologos);

$especialidades = [];

foreach ($odontologos as $odontologo) {
    $especialidad = trim((string) ($odontologo["especialidad"] ?? ""));

    if ($especialidad !== "") {
        $especialidades[$especialidad] = true;
    }
}

$totalEspecialidades = count($especialidades);

$totalConsultorios = count(array_filter($odontologos, static function ($odontologo) {
    return trim((string) ($odontologo["consultorio"] ?? "")) !== "";
}));

$totalSlugs = count(array_filter($odontologos, static function ($odontologo) {
    return trim((string) ($odontologo["slug_publico"] ?? "")) !== "";
}));

$pageTitle = "Odontólogos";
$pageSection = "Odontólogos";
$pageAccent = "odontologos";
$activeNav = "odontologos";

require __DIR__ . "/../partials/head.php";

?>

<section class="page-hero">
    <div class="page-hero__copy">
        <span class="eyebrow">Equipo odontológico</span>
        <h1>Odontólogos registrados</h1>
        <p>
            Organiza especialidades, consultorios y perfiles públicos.
            Un equipo bien coordinado para cuidar cada sonrisa.
        </p>

        <div class="page-hero__actions">
            <a class="btn btn--primary" href="<?= sco_href('view/odontologo/form_odontologo.php') ?>">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
                Registrar odontólogo
            </a>
            <a class="btn btn--ghost" href="#listado">Ver listado</a>
        </div>
    </div>

    <?php
    $mediaTitle = 'Especialistas que cuidan cada sonrisa';
    $mediaText = 'Reemplaza assests/img/proyecto.jpg con una fotografía del equipo o del consultorio.';
    $mediaLabel = 'Imagen del proyecto';
    $mediaClass = 'page-hero__media';
    require __DIR__ . '/../partials/media-slot.php';
    ?>
</section>

<div class="metric-grid">
    <article class="metric-card">
        <span class="metric-card__icon">
            <svg viewBox="0 0 448 512" aria-hidden="true"><path d="M443.98 96.01c0-56.43-45.53-96.01-112.9-96.01-44.65 0-60.15 25-92.08 25-31.03 0-48.88-25-92.08-25C77.55 0 32 39.59 32 96.01c0 39.02 16.93 70.15 38.33 111.1 15.68 30.02 25.75 66.62 31.28 105.8C108.7 366.2 118.1 512 156.5 512c24.41 0 31.51-21.11 38.51-49.11 5.95-23.84 12.06-48.39 29.09-48.39 17.03 0 23.14 24.55 29.09 48.39 7 28 14.1 49.11 38.51 49.11 38.4 0 47.8-145.8 54.89-198.1 5.53-39.2 15.6-75.79 31.28-105.8 21.4-40.92 38.33-72.05 38.33-111.09z"/></svg>
        </span>
        <div class="metric-card__body">
            <span class="metric-card__label">Total registrados</span>
            <span class="metric-card__value"><span data-counter="<?= $totalOdontologos ?>">0</span></span>
            <span class="metric-card__note">Especialistas en el sistema</span>
        </div>
    </article>

    <article class="metric-card">
        <span class="metric-card__icon">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3v18M3 12h18"/><circle cx="12" cy="12" r="9"/></svg>
        </span>
        <div class="metric-card__body">
            <span class="metric-card__label">Especialidades</span>
            <span class="metric-card__value"><span data-counter="<?= $totalEspecialidades ?>">0</span></span>
            <span class="metric-card__note">Áreas de atención</span>
        </div>
    </article>

    <article class="metric-card">
        <span class="metric-card__icon">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 21h18M5 21V7l7-4 7 4v14"/><path d="M9 21v-6h6v6M9 11h.01M15 11h.01"/></svg>
        </span>
        <div class="metric-card__body">
            <span class="metric-card__label">Consultorios</span>
            <span class="metric-card__value"><span data-counter="<?= $totalConsultorios ?>">0</span></span>
            <span class="metric-card__note">Espacios asignados</span>
        </div>
    </article>

    <article class="metric-card">
        <span class="metric-card__icon">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M10 13a5 5 0 0 0 7.1 0l2-2a5 5 0 0 0-7.1-7.1L10.5 5.4"/><path d="M14 11a5 5 0 0 0-7.1 0l-2 2A5 5 0 0 0 12 20.1l1.5-1.5"/></svg>
        </span>
        <div class="metric-card__body">
            <span class="metric-card__label">Perfiles públicos</span>
            <span class="metric-card__value"><span data-counter="<?= $totalSlugs ?>">0</span></span>
            <span class="metric-card__note">Slugs disponibles</span>
        </div>
    </article>
</div>

<?php if ($mensaje !== null): ?>
    <div class="alert alert--<?= $tipo_mensaje === 'error' ? 'error' : 'success' ?>">
        <span class="alert__icon">
            <?php if ($tipo_mensaje === 'error'): ?>
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 9v4M12 17h.01"/><circle cx="12" cy="12" r="9"/></svg>
            <?php else: ?>
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>
            <?php endif; ?>
        </span>
        <div>
            <strong><?= $tipo_mensaje === 'error' ? 'No se pudo completar' : 'Operación exitosa' ?></strong>
            <span><?= sco_e($mensaje) ?></span>
        </div>
    </div>
<?php endif; ?>

<section class="panel" id="listado">
    <div class="panel__head">
        <div>
            <h2 class="panel__title">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 6h16M4 12h16M4 18h10"/></svg>
                Listado de odontólogos
                <span class="panel__count"><?= $totalOdontologos ?></span>
            </h2>
            <p class="panel__subtitle">
                <?= $busqueda !== ""
                    ? 'Resultados para “' . sco_e($busqueda) . '”.'
                    : 'Especialistas, consultorios y perfiles públicos.' ?>
            </p>
        </div>

        <div class="panel__tools">
            <form class="search" action="<?= sco_href('view/odontologo/lista_odontologos.php') ?>" method="GET" role="search">
                <div class="search__field">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
                    <input
                        type="search"
                        name="nombre"
                        value="<?= sco_e($busqueda) ?>"
                        placeholder="Buscar por nombre o especialidad..."
                        aria-label="Buscar odontólogos"
                    >
                </div>
                <button class="btn btn--primary btn--sm" type="submit">Buscar</button>
                <?php if ($busqueda !== ""): ?>
                    <a class="btn btn--ghost btn--sm" href="<?= sco_href('view/odontologo/lista_odontologos.php') ?>">Limpiar</a>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <?php if ($totalOdontologos > 0): ?>

        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Odontólogo</th>
                        <th>Especialidad</th>
                        <th>Consultorio</th>
                        <th>Slug público</th>
                        <th>Acciones</th>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach ($odontologos as $odontologo): ?>
                        <?php
                        $odontologoId = (int) $odontologo["id"];
                        $odontologoNombre = (string) ($odontologo["nombre"] ?? "");
                        $odontologoEspecialidad = trim((string) ($odontologo["especialidad"] ?? ""));
                        $odontologoConsultorio = trim((string) ($odontologo["consultorio"] ?? ""));
                        $odontologoSlug = trim((string) ($odontologo["slug_publico"] ?? ""));
                        ?>
                        <tr>
                            <td data-label="ID">
                                <span class="row-id">#<?= $odontologoId ?></span>
                            </td>

                            <td data-label="Odontólogo">
                                <div class="person">
                                    <span class="avatar" style="--avatar-hue: <?= ($odontologoId * 61) % 360 ?>;">
                                        <?= sco_e(sco_initials($odontologoNombre)) ?>
                                    </span>
                                    <div class="person__meta">
                                        <strong><?= sco_e($odontologoNombre) ?></strong>
                                        <span>Especialista</span>
                                    </div>
                                </div>
                            </td>

                            <td data-label="Especialidad">
                                <?php if ($odontologoEspecialidad !== ""): ?>
                                    <span class="chip chip--accent"><?= sco_e($odontologoEspecialidad) ?></span>
                                <?php else: ?>
                                    <span class="text-muted">Sin especialidad</span>
                                <?php endif; ?>
                            </td>

                            <td data-label="Consultorio">
                                <?php if ($odontologoConsultorio !== ""): ?>
                                    <?= sco_e($odontologoConsultorio) ?>
                                <?php else: ?>
                                    <span class="text-muted">Sin asignar</span>
                                <?php endif; ?>
                            </td>

                            <td data-label="Slug público">
                                <?php if ($odontologoSlug !== ""): ?>
                                    <code class="slug" title="<?= sco_e($odontologoSlug) ?>"><?= sco_e($odontologoSlug) ?></code>
                                <?php else: ?>
                                    <span class="text-muted">No disponible</span>
                                <?php endif; ?>
                            </td>

                            <td data-label="Acciones">
                                <div class="row-actions">
                                    <a class="action action--edit" href="<?= sco_href('app/logica_odontologo.php?accion=editar&id=' . $odontologoId) ?>">
                                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4z"/></svg>
                                        Editar
                                    </a>

                                    <form
                                        class="row-action-form"
                                        action="<?= sco_href('app/logica_odontologo.php') ?>"
                                        method="POST"
                                        onsubmit="return confirm('¿Seguro que deseas eliminar este odontólogo?');"
                                    >
                                        <input type="hidden" name="accion" value="eliminar">
                                        <input type="hidden" name="id" value="<?= $odontologoId ?>">
                                        <button class="action action--danger" type="submit">
                                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 6h18M8 6V4h8v2M19 6l-1 14H6L5 6M10 11v5M14 11v5"/></svg>
                                            Eliminar
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    <?php else: ?>

        <div class="empty-state">
            <span class="empty-state__icon">
                <svg viewBox="0 0 448 512" aria-hidden="true"><path d="M443.98 96.01c0-56.43-45.53-96.01-112.9-96.01-44.65 0-60.15 25-92.08 25-31.03 0-48.88-25-92.08-25C77.55 0 32 39.59 32 96.01c0 39.02 16.93 70.15 38.33 111.1 15.68 30.02 25.75 66.62 31.28 105.8C108.7 366.2 118.1 512 156.5 512c24.41 0 31.51-21.11 38.51-49.11 5.95-23.84 12.06-48.39 29.09-48.39 17.03 0 23.14 24.55 29.09 48.39 7 28 14.1 49.11 38.51 49.11 38.4 0 47.8-145.8 54.89-198.1 5.53-39.2 15.6-75.79 31.28-105.8 21.4-40.92 38.33-72.05 38.33-111.09z"/></svg>
            </span>
            <h3><?= $busqueda !== "" ? 'Sin coincidencias' : 'Aún no hay odontólogos' ?></h3>
            <p>
                <?= $busqueda !== ""
                    ? 'No encontramos odontólogos con ese criterio. Prueba con otra búsqueda.'
                    : 'Registra al primer especialista para empezar a organizar el equipo odontológico.' ?>
            </p>
            <a class="btn btn--primary" href="<?= sco_href('view/odontologo/form_odontologo.php') ?>">
                Registrar odontólogo
            </a>
        </div>

    <?php endif; ?>
</section>

<?php require __DIR__ . "/../partials/footer.php"; ?>
