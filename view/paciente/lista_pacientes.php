<?php

session_start();

require_once __DIR__ . "/../../config/database.php";

// Recuperar mensajes de la operación anterior.
$mensaje = $_SESSION["mensaje"] ?? null;
$tipo_mensaje = $_SESSION["tipo_mensaje"] ?? "exito";

unset($_SESSION["mensaje"], $_SESSION["tipo_mensaje"]);

// Obtener el término de búsqueda.
$busqueda = trim($_GET["nombre"] ?? "");

try {

    if ($busqueda !== "") {

        $sql = "SELECT id, nombre, email, alergias
                FROM paciente
                WHERE nombre ILIKE :nombre
                ORDER BY id";

        $consulta = $conexion->prepare($sql);
        $consulta->execute([
            ":nombre" => "%" . $busqueda . "%"
        ]);

    } else {

        $sql = "SELECT id, nombre, email, alergias
                FROM paciente
                ORDER BY id";

        $consulta = $conexion->query($sql);
    }

    $pacientes = $consulta->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {

    error_log("Error al listar pacientes: " . $e->getMessage());

    $pacientes = [];
    $mensaje = "No se pudo cargar el listado de pacientes.";
    $tipo_mensaje = "error";
}

$totalPacientes = count($pacientes);

$conAlergias = count(array_filter($pacientes, static function ($paciente) {
    return trim((string) ($paciente["alergias"] ?? "")) !== "";
}));

$sinAlergias = $totalPacientes - $conAlergias;

$conEmail = count(array_filter($pacientes, static function ($paciente) {
    return trim((string) ($paciente["email"] ?? "")) !== "";
}));

$pageTitle = "Pacientes";
$pageSection = "Pacientes";
$pageAccent = "pacientes";
$activeNav = "pacientes";

require __DIR__ . "/../partials/head.php";

?>

<section class="page-hero">
    <div class="page-hero__copy">
        <span class="eyebrow">Gestión de pacientes</span>
        <h1>Pacientes registrados</h1>
        <p>
            Consulta, actualiza y organiza la información de cada paciente.
            Todo lo que necesitas para brindar una atención cercana y ordenada.
        </p>

        <div class="page-hero__actions">
            <a class="btn btn--primary" href="<?= sco_href('view/paciente/form_paciente.php') ?>">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
                Registrar paciente
            </a>
            <a class="btn btn--ghost" href="#listado">Ver listado</a>
        </div>
    </div>

    <?php
    $mediaTitle = 'Tu consultorio, en una imagen';
    $mediaText = 'Reemplaza assests/img/proyecto.jpg con la fotografía principal del proyecto.';
    $mediaLabel = 'Imagen del proyecto';
    $mediaClass = 'page-hero__media';
    require __DIR__ . '/../partials/media-slot.php';
    ?>
</section>

<div class="metric-grid">
    <article class="metric-card">
        <span class="metric-card__icon">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
        </span>
        <div class="metric-card__body">
            <span class="metric-card__label">Total registrados</span>
            <span class="metric-card__value"><span data-counter="<?= $totalPacientes ?>">0</span></span>
            <span class="metric-card__note">Pacientes en el sistema</span>
        </div>
    </article>

    <article class="metric-card">
        <span class="metric-card__icon">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><path d="M12 9v4M12 17h.01"/></svg>
        </span>
        <div class="metric-card__body">
            <span class="metric-card__label">Con alergias</span>
            <span class="metric-card__value"><span data-counter="<?= $conAlergias ?>">0</span></span>
            <span class="metric-card__note">Requieren seguimiento</span>
        </div>
    </article>

    <article class="metric-card">
        <span class="metric-card__icon">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="m9 12 2 2 4-4"/></svg>
        </span>
        <div class="metric-card__body">
            <span class="metric-card__label">Sin alergias</span>
            <span class="metric-card__value"><span data-counter="<?= $sinAlergias ?>">0</span></span>
            <span class="metric-card__note">Sin restricciones registradas</span>
        </div>
    </article>

    <article class="metric-card">
        <span class="metric-card__icon">
            <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg>
        </span>
        <div class="metric-card__body">
            <span class="metric-card__label">Con correo</span>
            <span class="metric-card__value"><span data-counter="<?= $conEmail ?>">0</span></span>
            <span class="metric-card__note">Contacto disponible</span>
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
                Listado de pacientes
                <span class="panel__count"><?= $totalPacientes ?></span>
            </h2>
            <p class="panel__subtitle">
                <?= $busqueda !== ""
                    ? 'Resultados para “' . sco_e($busqueda) . '”.'
                    : 'Todos los pacientes registrados en el sistema.' ?>
            </p>
        </div>

        <div class="panel__tools">
            <form class="search" action="<?= sco_href('view/paciente/lista_pacientes.php') ?>" method="GET" role="search">
                <div class="search__field">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
                    <input
                        type="search"
                        name="nombre"
                        value="<?= sco_e($busqueda) ?>"
                        placeholder="Buscar por nombre..."
                        aria-label="Buscar pacientes por nombre"
                    >
                </div>
                <button class="btn btn--primary btn--sm" type="submit">Buscar</button>
                <?php if ($busqueda !== ""): ?>
                    <a class="btn btn--ghost btn--sm" href="<?= sco_href('view/paciente/lista_pacientes.php') ?>">Limpiar</a>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <?php if ($totalPacientes > 0): ?>

        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Paciente</th>
                        <th>Correo electrónico</th>
                        <th>Alergias</th>
                        <th>Acciones</th>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach ($pacientes as $paciente): ?>
                        <?php
                        $pacienteId = (int) $paciente["id"];
                        $pacienteNombre = (string) ($paciente["nombre"] ?? "");
                        $pacienteEmail = trim((string) ($paciente["email"] ?? ""));
                        $pacienteAlergias = trim((string) ($paciente["alergias"] ?? ""));
                        ?>
                        <tr>
                            <td data-label="ID">
                                <span class="row-id">#<?= $pacienteId ?></span>
                            </td>

                            <td data-label="Paciente">
                                <div class="person">
                                    <span class="avatar" style="--avatar-hue: <?= ($pacienteId * 47) % 360 ?>;">
                                        <?= sco_e(sco_initials($pacienteNombre)) ?>
                                    </span>
                                    <div class="person__meta">
                                        <strong><?= sco_e($pacienteNombre) ?></strong>
                                        <span>Paciente registrado</span>
                                    </div>
                                </div>
                            </td>

                            <td data-label="Correo">
                                <?php if ($pacienteEmail !== ""): ?>
                                    <a class="link" href="mailto:<?= sco_e($pacienteEmail) ?>"><?= sco_e($pacienteEmail) ?></a>
                                <?php else: ?>
                                    <span class="text-muted">Sin correo</span>
                                <?php endif; ?>
                            </td>

                            <td data-label="Alergias">
                                <?php if ($pacienteAlergias !== ""): ?>
                                    <span class="chip chip--warning"><?= sco_e($pacienteAlergias) ?></span>
                                <?php else: ?>
                                    <span class="chip chip--muted">Sin alergias</span>
                                <?php endif; ?>
                            </td>

                            <td data-label="Acciones">
                                <div class="row-actions">
                                    <a class="action action--edit" href="<?= sco_href('app/logica_paciente.php?accion=editar&id=' . $pacienteId) ?>">
                                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4z"/></svg>
                                        Editar
                                    </a>

                                    <form
                                        class="row-action-form"
                                        action="<?= sco_href('app/logica_paciente.php') ?>"
                                        method="POST"
                                        onsubmit="return confirm('¿Deseas eliminar este paciente?');"
                                    >
                                        <input type="hidden" name="accion" value="eliminar">
                                        <input type="hidden" name="id" value="<?= $pacienteId ?>">
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
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            </span>
            <h3><?= $busqueda !== "" ? 'Sin coincidencias' : 'Aún no hay pacientes' ?></h3>
            <p>
                <?= $busqueda !== ""
                    ? 'No encontramos pacientes con ese nombre. Prueba con otra búsqueda.'
                    : 'Registra el primer paciente para empezar a organizar la información del consultorio.' ?>
            </p>
            <a class="btn btn--primary" href="<?= sco_href('view/paciente/form_paciente.php') ?>">
                Registrar paciente
            </a>
        </div>

    <?php endif; ?>
</section>

<?php require __DIR__ . "/../partials/footer.php"; ?>
