<?php $titulo = 'Página no encontrada'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($titulo); ?> | DENTIX</title>
    <link rel="stylesheet" href="<?php echo asset('css/index.css'); ?>">
</head>
<body class="admin-body" style="display:grid;place-items:center;min-height:100vh;">
    <main style="text-align:center;max-width:520px;padding:32px;">
        <h1 style="font-size:4rem;margin:0;">404</h1>
        <h2><?php echo e($titulo); ?></h2>
        <p>La ruta que intentas abrir no existe dentro de DENTIX.</p>
        <a class="btn btn--primary" href="<?php echo url('inicio'); ?>">Volver al inicio</a>
    </main>
</body>
</html>
