<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agendar cita - DENTIX</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@500;600;700;800&family=Sora:wght@700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="<?php echo asset('css/admin.css'); ?>">
    <link rel="stylesheet" href="<?php echo asset('css/paciente.css?v=20261110-vivid'); ?>">
</head>
<body class="admin-body patient-auth-body">
    <main class="patient-auth-card patient-booking-card">
        <a href="<?php echo url("perfil-paciente"); ?>" class="patient-auth-back patient-booking-back"><i class="fa-solid fa-arrow-left"></i> Volver a mi perfil</a>
        <div class="patient-auth-heading">
            <span class="patient-auth-icon"><i class="fa-solid fa-calendar-plus"></i></span>
            <span class="eyebrow">Agenda DENTIX</span>
            <h1>Solicita tu cita</h1>
            <p>Hola, <?php echo htmlspecialchars($paciente['nombre'], ENT_QUOTES, 'UTF-8'); ?>. Elige el tratamiento y el horario que prefieres; recepción asignará al odontólogo.</p>
        </div>

        <?php if ($error !== ''): ?>
            <div class="alert alert-danger py-2 small" role="alert">
                <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php endif; ?>

        <?php if ($servicios === []): ?>
            <div class="alert alert-info small" role="status">En este momento no hay servicios disponibles para agendar.</div>
        <?php else: ?>
            <form method="POST" action="<?php echo url("portal-paciente"); ?>">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
                <div class="mb-3">
                    <label for="servicio_id" class="form-label small fw-bold">Servicio odontológico</label>
                    <select class="form-select" id="servicio_id" name="servicio_id" required>
                        <option value="">Selecciona un tratamiento</option>
                        <?php foreach ($servicios as $servicio): ?>
                            <option value="<?php echo (int) $servicio['id']; ?>" <?php echo ($selectedServicioId === (string) $servicio['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($servicio['nombre'], ENT_QUOTES, 'UTF-8'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="row g-3 mb-4">
                    <div class="col-sm-6">
                        <label for="fecha" class="form-label small fw-bold">Fecha</label>
                        <input type="date" class="form-control" id="fecha" name="fecha" min="<?php echo date('Y-m-d'); ?>" value="<?php echo htmlspecialchars($fechaIngresadaForm, ENT_QUOTES, 'UTF-8'); ?>" required>
                    </div>
                    <div class="col-sm-6">
                        <label for="hora" class="form-label small fw-bold">Hora</label>
                        <input type="time" class="form-control" id="hora" name="hora" min="08:00" max="19:30" step="1800" value="<?php echo htmlspecialchars($horaIngresadaForm, ENT_QUOTES, 'UTF-8'); ?>" required>
                    </div>
                </div>
                <div class="patient-booking-notice">
                    <i class="fa-solid fa-circle-info"></i>
                    <span>Tu solicitud aparecerá como pendiente mientras recepción confirma la cita y asigna al odontólogo.</span>
                </div>
                <button type="submit" class="btn btn--primary w-100 py-2 fw-bold mt-3">
                    <i class="fa-solid fa-calendar-check me-2"></i> Solicitar cita
                </button>
            </form>
        <?php endif; ?>
    </main>
    <script src="<?php echo asset('js/dentix-i18n.js?v=20261110-cur'); ?>"></script>
</body>
</html>
