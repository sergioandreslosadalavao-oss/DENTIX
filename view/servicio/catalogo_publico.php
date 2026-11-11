<?php
require_once __DIR__ . '/../../config/database.php';

try {
    $servicios = $conexion->query(
        'SELECT id, nombre, descripcion, duracion_min, precio
         FROM servicio
         ORDER BY nombre'
    )->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log('Error al cargar el catálogo público de tratamientos: ' . $e->getMessage());
    http_response_code(500);
    exit('No se pudo cargar el catálogo de tratamientos. Inténtalo nuevamente más tarde.');
}

$iconosTratamiento = [
    'limpieza' => 'fa-solid fa-hand-sparkles',
    'blanqueamiento' => 'fa-solid fa-wand-magic-sparkles',
    'extraccion' => 'fa-solid fa-tooth',
    'endodoncia' => 'fa-solid fa-shield-halved',
    'ortodoncia' => 'fa-solid fa-teeth',
    'consulta' => 'fa-solid fa-user-doctor',
];

function iconoTratamiento(string $nombre, array $iconos): string
{
    $nombreNormalizado = mb_strtolower($nombre, 'UTF-8');

    foreach ($iconos as $palabra => $icono) {
        if (str_contains($nombreNormalizado, $palabra)) {
            return $icono;
        }
    }

    return 'fa-solid fa-tooth';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tratamientos odontológicos | DENTIX</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@500;600;700;800&family=Sora:wght@700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="../../assests/css/index.css?v=20261103-tratamientos">
</head>
<body class="treatments-page">
    <header class="site-header is-scrolled">
        <div class="header__inner">
            <a href="../../index.php" class="brand" aria-label="DENTIX, volver al inicio">
                <span class="brand__mark"><i class="fa-solid fa-tooth"></i></span>
                <span class="brand__text">DENTIX<small>Clínica odontológica</small></span>
            </a>
            <div class="header__cta">
                <a href="../paciente/login_paciente.php?next=portal_paciente.php" class="btn btn--primary btn--small">
                    <i class="fa-solid fa-calendar-plus" aria-hidden="true"></i> Solicitar una cita
                </a>
            </div>
        </div>
    </header>

    <main>
        <section class="treatments-hero">
            <div class="container">
                <a class="treatments-back" href="../../index.php"><i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Volver al inicio</a>
                <div class="section-head section-head--center">
                    <span class="eyebrow">Atención para cada sonrisa</span>
                    <h1>Tratamientos para cuidar <em>tu salud oral</em></h1>
                    <p>Conoce nuestros servicios odontológicos, sus tiempos estimados y tarifas de referencia. Nuestro equipo puede orientarte para encontrar la atención adecuada.</p>
                </div>
                <div class="treatments-trust">
                    <span><i class="fa-solid fa-circle-check" aria-hidden="true"></i> Información clara</span>
                    <span><i class="fa-solid fa-user-doctor" aria-hidden="true"></i> Atención profesional</span>
                    <span><i class="fa-solid fa-calendar-check" aria-hidden="true"></i> Solicitud en línea</span>
                </div>
            </div>
        </section>

        <section class="section treatments-section" id="tratamientos">
            <div class="container">
                <div class="treatments-section__heading">
                    <div>
                        <span class="eyebrow">Servicios DENTIX</span>
                        <h2>Conoce nuestras <em>opciones de atención</em></h2>
                    </div>
                    <p>La duración y el valor pueden variar según la valoración odontológica y las necesidades de cada paciente.</p>
                </div>

                <?php if ($servicios === []): ?>
                    <div class="treatments-empty" role="status">
                        <span><i class="fa-solid fa-tooth" aria-hidden="true"></i></span>
                        <h2>Estamos actualizando el catálogo</h2>
                        <p>Comunícate con nuestro equipo para recibir información sobre los tratamientos disponibles.</p>
                    </div>
                <?php else: ?>
                    <div class="treatments-grid">
                        <?php foreach ($servicios as $indice => $servicio): ?>
                            <article class="treatment-card">
                                <span class="treatment-card__number"><?php echo str_pad((string) ($indice + 1), 2, '0', STR_PAD_LEFT); ?></span>
                                <span class="treatment-card__icon" aria-hidden="true">
                                    <i class="<?php echo htmlspecialchars(iconoTratamiento($servicio['nombre'], $iconosTratamiento), ENT_QUOTES, 'UTF-8'); ?>"></i>
                                </span>
                                <span class="treatment-card__eyebrow">Atención odontológica</span>
                                <h3><?php echo htmlspecialchars($servicio['nombre'], ENT_QUOTES, 'UTF-8'); ?></h3>
                                <p class="treatment-card__description">
                                    <?php echo htmlspecialchars(trim((string) $servicio['descripcion']) !== '' ? $servicio['descripcion'] : 'Consulta con nuestro equipo para conocer los detalles de este tratamiento y saber si es adecuado para ti.', ENT_QUOTES, 'UTF-8'); ?>
                                </p>
                                <div class="treatment-card__details">
                                    <span><i class="fa-regular fa-clock" aria-hidden="true"></i> Duración estimada</span>
                                    <strong><?php echo (int) $servicio['duracion_min']; ?> minutos</strong>
                                </div>
                                <div class="treatment-card__price">
                                    <span>Tarifa de referencia</span>
                                    <strong data-cop-price="<?php echo htmlspecialchars((string) (float) $servicio['precio'], ENT_QUOTES, 'UTF-8'); ?>" data-price-format="card">$<?php echo number_format((float) $servicio['precio'], 0, ',', '.'); ?> <small>COP</small></strong>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <section class="treatments-cta">
            <div class="container treatments-cta__inner">
                <div>
                    <span class="eyebrow eyebrow--light">Estamos para ayudarte</span>
                    <h2>¿Listo para cuidar tu sonrisa?</h2>
                    <p>Solicita una cita y recepción te ayudará con la confirmación y asignación del profesional.</p>
                </div>
                <a href="../paciente/login_paciente.php?next=portal_paciente.php" class="btn btn--white">
                    <i class="fa-solid fa-calendar-plus" aria-hidden="true"></i> Solicitar una cita
                </a>
            </div>
        </section>
    </main>

    <footer class="site-footer">
        <div class="container footer__inner">
            <div class="footer__brand">
                <a href="../../index.php" class="brand brand--footer">
                    <span class="brand__mark"><i class="fa-solid fa-tooth"></i></span>
                    <span class="brand__text">DENTIX</span>
                </a>
                <p>Atención odontológica con calidez, claridad y compromiso con tu salud oral.</p>
            </div>
            <div class="footer__nav">
                <a href="../../index.php">Inicio</a>
                <a href="../paciente/login_paciente.php">Portal del paciente</a>
            </div>
            <div class="footer__copy">&copy; 2026 DENTIX. Todos los derechos reservados.</div>
        </div>
    </footer>
    <script src="../../assests/js/dentix-i18n.js?v=20261110-cur"></script>
</body>
</html>
