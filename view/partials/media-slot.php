<?php

require_once __DIR__ . '/bootstrap.php';

$mediaImage = is_file(SCO_PROJECT_IMAGE_FILE)
    ? SCO_PROJECT_IMAGE_URL
    : SCO_PROJECT_PLACEHOLDER;

$mediaTitle = $mediaTitle ?? 'Tu consultorio, en una imagen';
$mediaText = $mediaText ?? 'Reemplaza el archivo assests/img/proyecto.jpg con tu fotografía.';
$mediaLabel = $mediaLabel ?? 'Imagen del proyecto';
$mediaClass = $mediaClass ?? '';

?>
<figure class="media-slot <?= sco_e($mediaClass) ?>">
    <img
        class="media-slot__img"
        src="<?= sco_e($mediaImage) ?>"
        alt="Imagen del proyecto SCO"
        loading="lazy"
    >
    <figcaption class="media-slot__overlay">
        <span class="media-slot__eyebrow"><?= sco_e($mediaLabel) ?></span>
        <strong><?= sco_e($mediaTitle) ?></strong>
        <span><?= sco_e($mediaText) ?></span>
    </figcaption>
</figure>
