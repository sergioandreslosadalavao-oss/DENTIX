<?php
require_once __DIR__ . '/../../app/paciente_auth.php';
iniciar_sesion_paciente();

if (!paciente_autenticado()) {
    header('Location: login_paciente.php?next=portal_paciente.php');
    exit;
}

header('Cache-Control: no-store, private');
header('Pragma: no-cache');

require_once __DIR__ . '/../../config/database.php';

$error = '';
$servicios = [];
$paciente = null;

try {
    $stmt = $conexion->prepare('SELECT id, nombre FROM paciente WHERE id = ?');
    $stmt->execute([$_SESSION['paciente_id']]);
    $paciente = $stmt->fetch();

    if (!$paciente) {
        $_SESSION = [];
        session_destroy();
        header('Location: login_paciente.php');
        exit;
    }

    $servicios = $conexion->query('SELECT id, nombre FROM servicio ORDER BY nombre')->fetchAll();
} catch (PDOException $e) {
    error_log('Error al cargar opciones de cita: ' . $e->getMessage());
    http_response_code(500);
    exit('No se pudo cargar el portal de citas. Inténtalo nuevamente más tarde.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $servicioId = is_string($_POST['servicio_id'] ?? null) ? trim($_POST['servicio_id']) : '';
    $fecha = is_string($_POST['fecha'] ?? null) ? trim($_POST['fecha']) : '';
    $hora = is_string($_POST['hora'] ?? null) ? trim($_POST['hora']) : '';

    if (!validar_csrf_paciente()) {
        $error = 'La sesión del formulario venció. Recarga la página e inténtalo de nuevo.';
    } elseif (!ctype_digit($servicioId)) {
        $error = 'Selecciona un servicio válido.';
    } else {
        $fechaIngresada = DateTimeImmutable::createFromFormat('!Y-m-d', $fecha);
        $fechaValida = $fechaIngresada !== false
            && $fechaIngresada->format('Y-m-d') === $fecha
            && $fecha >= date('Y-m-d');
        $horaValida = preg_match('/^(?:0[89]|1[0-9]):(?:00|30)$/', $hora) === 1;

        if (!$fechaValida || !$horaValida) {
            $error = 'Elige una fecha válida y un horario entre 08:00 y 19:30, cada 30 minutos.';
        } else {
            try {
                $stmt = $conexion->prepare('SELECT id FROM servicio WHERE id = ?');
                $stmt->execute([$servicioId]);
                $servicioExiste = $stmt->fetchColumn();

                if (!$servicioExiste) {
                    $error = 'El servicio seleccionado ya no está disponible.';
                } else {
                    $stmt = $conexion->prepare(
                        "SELECT id
                         FROM cita
                         WHERE paciente_id = ?
                           AND fecha = ?
                           AND hora = ?
                           AND estado IN ('pendiente', 'confirmada')
                         LIMIT 1"
                    );
                    $stmt->execute([$paciente['id'], $fecha, $hora]);

                    if ($stmt->fetchColumn()) {
                        $error = 'Ya tienes una cita pendiente o confirmada en ese horario.';
                    } else {
                        $stmt = $conexion->prepare(
                            "INSERT INTO cita (paciente_id, odontologo_id, servicio_id, fecha, hora, estado)
                             VALUES (?, NULL, ?, ?, ?, 'pendiente')"
                        );
                        $stmt->execute([
                            $paciente['id'],
                            $servicioId,
                            $fecha,
                            $hora,
                        ]);
                        $_SESSION['paciente_flash'] = 'Tu solicitud quedó pendiente. Recepción confirmará la cita y asignará al odontólogo.';
                        header('Location: perfil_paciente.php');
                        exit;
                    }
                }
            } catch (PDOException $e) {
                error_log('Error al crear cita desde el portal del paciente: ' . $e->getMessage());
                $error = 'No se pudo guardar la cita. Verifica los datos o intenta con otro horario.';
            }
        }
    }
}

$csrfToken = token_csrf_paciente();
$selectedServicioId = is_string($_POST['servicio_id'] ?? null) ? $_POST['servicio_id'] : '';
$fechaIngresadaForm = is_string($_POST['fecha'] ?? null) ? $_POST['fecha'] : '';
$horaIngresadaForm = is_string($_POST['hora'] ?? null) ? $_POST['hora'] : '';
?>
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
    <link rel="stylesheet" href="../../assests/css/admin.css">
    <link rel="stylesheet" href="../../assests/css/paciente.css">
</head>
<body class="admin-body patient-auth-body">
    <main class="patient-auth-card patient-booking-card">
        <a href="perfil_paciente.php" class="patient-auth-back patient-booking-back"><i class="fa-solid fa-arrow-left"></i> Volver a mi perfil</a>
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
            <form method="POST" action="portal_paciente.php">
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
</body>
</html>
