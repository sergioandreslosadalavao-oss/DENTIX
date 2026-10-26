<?php

session_start();

require_once __DIR__ . "/../../config/database.php";

$mensaje = $_SESSION["mensaje"] ?? null;
$tipo_mensaje = $_SESSION["tipo_mensaje"] ?? "exito";

unset($_SESSION["mensaje"], $_SESSION["tipo_mensaje"]);

$busqueda = trim($_GET["nombre"] ?? "");

try {

    if ($busqueda !== "") {

        $consulta = $conexion->prepare(
            "SELECT id, nombre, duracion_min, precio
             FROM servicio
             WHERE nombre ILIKE :nombre
             ORDER BY id ASC"
        );

        $consulta->execute([":nombre" => "%" . $busqueda . "%"]);

    } else {

        $consulta = $conexion->query(
            "SELECT id, nombre, duracion_min, precio
             FROM servicio
             ORDER BY id ASC"
        );
    }

    $servicios = $consulta->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {

    error_log("Error al listar servicios: " . $e->getMessage());

    $servicios = [];
    $mensaje = "No se pudo cargar el listado de servicios.";
    $tipo_mensaje = "error";
}

$totalServicios = count($servicios);

$duracionTotal = array_sum(array_map(static function ($servicio) {
    return (int) ($servicio["duracion_min"] ?? 0);
}, $servicios));

$precioTotal = array_sum(array_map(static function ($servicio) {
    return (float) ($servicio["precio"] ?? 0);
}, $servicios));

$duracionPromedio = $totalServicios > 0 ? (int) round($duracionTotal / $totalServicios) : 0;
$precioPromedio = $totalServicios > 0 ? (int) round($precioTotal / $totalServicios) : 0;

$precioMaximo = 0;

foreach ($servicios as $servicio) {
    $precioMaximo = max($precioMaximo, (float) ($servicio["precio"] ?? 0));
}

$precioMaximo = (int) round($precioMaximo);

$pageTitle = "Servicios";
$pageSection = "Servicios";
$pageAccent = "servicios";
$activeNav = "servicios";

require __DIR__ . "/../partials/head.php";

?>

<section class="page-hero">
    <div class="page-hero__copy">
        <span class="eyebrow">Catálogo de servicios</span>
        <h1>Servicios registrados</h1>
        <p>
            Gestiona tratamientos, duraciones y precios en un solo lugar.
            Un catálogo claro para una experiencia más ágil en el consultorio.
        </p>

        <div class="page-hero__actions">
            <a class="btn btn--primary" href="<?= sco_href('view/servicio/form_servicio.php') ?>">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
                Registrar servicio
            </a>
            <a class="btn btn--ghost" href="#listado">Ver listado</a>
        </div>
    </div>

    <?php
    $mediaTitle = 'Tratamientos que transforman sonrisas';
    $mediaText = 'Reemplaza assests/img/proyecto.jpg con una fotografía del proyecto o del consultorio.';
    $mediaLabel = 'Imagen del proyecto';
    $mediaClass = 'page-hero__media';
    require __DIR__ . '/../partials/media-slot.php';
    ?>
</section>

<div class="metric-grid">
    <article class="metric-card">
        <span class="metric-card__icon">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3v18M3 12h18"/><circle cx="12" cy="12" r="9"/></svg>
        </span>
        <div class="metric-card__body">
            <span class="metric-card__label">Total de servicios</span>
            <span class="metric-card__value"><span data-counter="<?= $totalServicios ?>">0</span></span>
            <span class="metric-card__note">Tratamientos en el catálogo</span>
        </div>
    </article>

    <article class="metric-card">
        <span class="metric-card__icon">
            <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
        </span>
        <div class="metric-card__body">
            <span class="metric-card__label">Duración promedio</span>
            <span class="metric-card__value">
                <span data-counter="<?= $duracionPromedio ?>">0</span>
                <span class="metric-card__suffix">min</span>
            </span>
            <span class="metric-card__note">Tiempo medio por servicio</span>
        </div>
    </article>

    <article class="metric-card">
        <span class="metric-card__icon">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2v20M17 6H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
        </span>
        <div class="metric-card__body">
            <span class="metric-card__label">Precio promedio</span>
            <span class="metric-card__value">
                <span class="metric-card__prefix">$</span>
                <span data-counter="<?= $precioPromedio ?>">0</span>
            </span>
            <span class="metric-card__note">Valor medio en COP</span>
        </div>
    </article>

    <article class="metric-card">
        <span class="metric-card__icon">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m3 17 6-6 4 4 8-8"/><path d="M15 7h6v6"/></svg>
        </span>
        <div class="metric-card__body">
            <span class="metric-card__label">Precio máximo</span>
            <span class="metric-card__value">
                <span class="metric-card__prefix">$</span>
                <span data-counter="<?= $precioMaximo ?>">0</span>
            </span>
            <span class="metric-card__note">Servicio de mayor valor</span>
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
                Listado de servicios
                <span class="panel__count"><?= $totalServicios ?></span>
            </h2>
            <p class="panel__subtitle">
                <?= $busqueda !== ""
                    ? 'Resultados para “' . sco_e($busqueda) . '”.'
                    : 'Tratamientos, duraciones y precios del catálogo.' ?>
            </p>
        </div>

        <div class="panel__tools">
            <form class="search" action="<?= sco_href('view/servicio/lista_servicios.php') ?>" method="GET" role="search">
                <div class="search__field">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
                    <input
                        type="search"
                        name="nombre"
                        value="<?= sco_e($busqueda) ?>"
                        placeholder="Buscar por nombre..."
                        aria-label="Buscar servicios por nombre"
                    >
                </div>
                <button class="btn btn--primary btn--sm" type="submit">Buscar</button>
                <?php if ($busqueda !== ""): ?>
                    <a class="btn btn--ghost btn--sm" href="<?= sco_href('view/servicio/lista_servicios.php') ?>">Limpiar</a>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <?php if ($totalServicios > 0): ?>

        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Servicio</th>
                        <th>Duración</th>
                        <th>Precio</th>
                        <th>Acciones</th>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach ($servicios as $servicio): ?>
                        <?php
                        $servicioId = (int) $servicio["id"];
                        $servicioNombre = (string) ($servicio["nombre"] ?? "");
                        $servicioDuracion = (int) ($servicio["duracion_min"] ?? 0);
                        $servicioPrecio = (float) ($servicio["precio"] ?? 0);
                        ?>
                        <tr>
                            <td data-label="ID">
                                <span class="row-id">#<?= $servicioId ?></span>
                            </td>

                            <td data-label="Servicio">
                                <div class="person">
                                    <span class="avatar" style="--avatar-hue: <?= ($servicioId * 83) % 360 ?>;">
                                        <?= sco_e(sco_initials($servicioNombre)) ?>
                                    </span>
                                    <div class="person__meta">
                                        <strong><?= sco_e($servicioNombre) ?></strong>
                                        <span>Tratamiento dental</span>
                                    </div>
                                </div>
                            </td>

                            <td data-label="Duración">
                                <span class="chip chip--info"><?= $servicioDuracion ?> min</span>
                            </td>

                            <td data-label="Precio">
                                <span class="money">$ <?= number_format($servicioPrecio, 2, ",", ".") ?></span>
                            </td>

                            <td data-label="Acciones">
                                <div class="row-actions">
                                    <a class="action action--edit" href="<?= sco_href('view/servicio/editar_servicio.php?id=' . $servicioId) ?>">
                                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4z"/></svg>
                                        Editar
                                    </a>

                                    <form
                                        class="row-action-form"
                                        action="<?= sco_href('app/logica_servicio.php') ?>"
                                        method="POST"
                                        onsubmit="return confirm('¿Seguro que deseas eliminar este servicio?');"
                                    >
                                        <input type="hidden" name="accion" value="eliminar">
                                        <input type="hidden" name="id" value="<?= $servicioId ?>">
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
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3v18M3 12h18"/><circle cx="12" cy="12" r="9"/></svg>
            </span>
            <h3><?= $busqueda !== "" ? 'Sin coincidencias' : 'Aún no hay servicios' ?></h3>
            <p>
                <?= $busqueda !== ""
                    ? 'No encontramos servicios con ese nombre. Prueba con otra búsqueda.'
                    : 'Registra el primer tratamiento para empezar a construir el catálogo del consultorio.' ?>
            </p>
            <a class="btn btn--primary" href="<?= sco_href('view/servicio/form_servicio.php') ?>">
                Registrar servicio
            </a>
        </div>

    <?php endif; ?>
</section>

<?php require __DIR__ . "/../partials/footer.php"; ?>
