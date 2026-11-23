<?php
// view/paciente/lista_pacientes.php - Listado de Pacientes con sincronización en vivo (RF-017 / RF-037)
require_once __DIR__ . '/../../app/recepcionista_auth.php';
requerir_recepcionista('../recepcionista/lista_recepcionista.php');
require_once "../../config/database.php";

// Endpoint JSON seguro para sincronización automática en tiempo real en la interfaz de Recepción (RF-037)
if (isset($_GET['ajax_pacientes']) && $_GET['ajax_pacientes'] === '1') {
    header('Content-Type: application/json; charset=UTF-8');
    header('Cache-Control: no-store, private');
    try {
        $rows = $conexion->query(
            "SELECT id, nombre, cedula, telefono, email, fecha_nac, alergias
             FROM paciente
             ORDER BY id DESC"
        )->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['ok' => true, 'pacientes' => $rows], JSON_UNESCAPED_UNICODE);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['ok' => false, 'pacientes' => []]);
    }
    exit;
}

$mensaje = $_SESSION['mensaje'] ?? null;
$tipo_mensaje = $_SESSION['tipo_mensaje'] ?? 'exito';
unset($_SESSION['mensaje'], $_SESSION['tipo_mensaje']);

if ($mensaje === null && ($_GET['registro'] ?? '') === 'exito') {
    $mensaje = 'Paciente registrado correctamente.';
    $tipo_mensaje = 'exito';
}

try {
    // Obtenemos los pacientes ordenados por ID de manera descendente
    $pacientes = $conexion->query(
        "SELECT id, nombre, cedula, telefono, email, fecha_nac, alergias
         FROM paciente
         ORDER BY id DESC"
    )->fetchAll();
} catch (PDOException $e) {
    $pacientes = [];
}

$csrfToken = token_csrf_recepcionista();
?>
<!DOCTYPE html>
<html lang="es" translate="no" class="notranslate">
<head>
    <meta charset="UTF-8">
    <meta name="google" content="notranslate">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pacientes - DENTIX</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@500;600;700;800&family=Sora:wght@700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/admin.css?v=20261103-2">
    <link rel="stylesheet" href="../../assets/css/index.css">
    <style>
        @keyframes dentixRowFlash {
            0%   { background-color: #ccfbf1; }
            65%  { background-color: #ecfdf5; }
            100% { background-color: transparent; }
        }
        tr.dentix-row-updated {
            animation: dentixRowFlash 2.6s ease-out;
        }
        @keyframes dentixLivePulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50%      { opacity: 0.45; transform: scale(0.85); }
        }
        .dentix-live-dot {
            display: inline-block;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #10b981;
            margin-right: 6px;
            animation: dentixLivePulse 1.8s infinite;
        }
    </style>
</head>
<body class="admin-body" data-accent="pacientes">
    <div class="page-progress"><span class="page-progress__bar"></span></div>
    <div class="app-shell">
        
        <!-- BARRA LATERAL IDÉNTICA -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar__top">
                <a href="../../index.php" class="brand">
                    <div class="brand__mark"><i class="fa-solid fa-tooth"></i></div>
                    <div class="brand__text">DENTIX<small>Panel Admin</small></div>
                </a>
                <button class="sidebar__close" id="sidebar-close"><svg viewBox="0 0 24 24"><path d="M18 6L6 18M6 6l12 12"/></svg></button>
            </div>
            <nav class="sidebar__nav">
                <span class="sidebar__label">Navegación</span>
                <a href="../../index.php" class="side-link"><span class="side-link__icon"><i class="fa-solid fa-house"></i></span>Inicio</a>
                <a href="lista_pacientes.php" class="side-link is-active"><span class="side-link__icon"><i class="fa-solid fa-hospital-user"></i></span>Pacientes</a>
                <a href="../odontologo/lista_odontologos.php" class="side-link"><span class="side-link__icon"><i class="fa-solid fa-user-doctor"></i></span>Odontólogos</a>
                <a href="../servicio/lista_servicios.php" class="side-link"><span class="side-link__icon"><i class="fa-solid fa-notes-medical"></i></span>Servicios</a>
                <a href="../cita/lista_citas.php" class="side-link"><span class="side-link__icon"><i class="fa-solid fa-calendar-days"></i></span>Citas</a>
                <a href="../recepcionista/reportes.php" class="side-link"><span class="side-link__icon"><i class="fa-solid fa-chart-column"></i></span>Reportes</a>
            </nav>
            <?php require __DIR__ . '/../recepcionista/boton_cerrar_sesion.php'; ?>
        </aside>

        <!-- CONTENIDO PRINCIPAL -->
        <main class="app-main">
            <header class="topbar">
                <button class="menu-btn" id="menu-toggle"><span></span><span></span><span></span></button>
                <div class="topbar__crumbs"><a href="../../index.php">Inicio</a><span>/</span><strong>Pacientes</strong></div>
            </header>

            <div class="page">
                <?php if ($mensaje !== null): ?>
                    <div class="alert <?php echo $tipo_mensaje === 'error' ? 'alert-danger' : 'alert-success'; ?> py-2 small mb-3 border-0 shadow-sm d-flex align-items-center">
                        <i class="fa-solid <?php echo $tipo_mensaje === 'error' ? 'fa-triangle-exclamation' : 'fa-circle-check'; ?> me-2"></i>
                        <div><?php echo htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8'); ?></div>
                    </div>
                <?php endif; ?>
                <div class="panel">
                    <div class="panel__head">
                        <div>
                            <h3 class="panel__title"><i class="fa-solid fa-hospital-user"></i> Listado de Pacientes</h3>
                            <p class="panel__subtitle">Consulta en vivo y depuración de expedientes. El registro y la edición pertenecen exclusivamente al titular (Ley 1581).</p>
                        </div>
                        <div class="panel__tools d-flex flex-wrap gap-2 align-items-center">
                            <span id="liveSyncBadge" class="badge px-3 py-2 rounded-pill fw-semibold" style="color: #065f46; background: #ecfdf5; border: 1px solid rgba(16, 185, 129, 0.25); font-size: 0.74rem;">
                                <span class="dentix-live-dot" aria-hidden="true"></span><span id="liveSyncText">Sincronización en vivo</span>
                            </span>
                            <span class="badge bg-teal bg-opacity-10 text-teal px-3 py-2 rounded-pill fw-semibold" style="color: #0a7a70; background: #e6f9f3; font-size: 0.75rem;">
                                <i class="fa-solid fa-user-shield me-1"></i> Registro y edición exclusivos del titular
                            </span>
                        </div>
                    </div>

                    <div class="table-wrap">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Nombre</th>
                                    <th>Documento</th>
                                    <th>Teléfono / Correo</th>
                                    <th>Nacimiento</th>
                                    <th>Alergias</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="tablaPacientesBody">
                                <?php if (count($pacientes) > 0): ?>
                                    <?php foreach ($pacientes as $i => $p): ?>
                                        <tr data-paciente-id="<?php echo (int) $p['id']; ?>">
                                            <td data-label="#"><span class="row-id"><?php echo $i + 1; ?></span></td>
                                            
                                            <!-- NOMBRE -->
                                            <td data-label="Nombre"><strong><?php echo htmlspecialchars($p['nombre']); ?></strong></td>
                                            
                                            <!-- DOCUMENTO (CÉDULA) -->
                                            <td data-label="Documento">
                                                <span class="badge bg-light text-dark border px-2 py-1 fw-bold">
                                                    <i class="fa-solid fa-id-card me-1 text-teal" style="color: #0d9488;"></i> <?php echo !empty($p['cedula']) ? htmlspecialchars($p['cedula']) : 'Sin registro'; ?>
                                                </span>
                                            </td>
                                            
                                            <!-- TELÉFONO Y CORREO -->
                                            <td data-label="Contacto">
                                                <div><i class="fa-solid fa-phone me-1 text-muted small"></i> <?php echo htmlspecialchars($p['telefono']); ?></div>
                                                <div class="text-muted small"><i class="fa-solid fa-envelope me-1"></i> <?php echo !empty($p['email']) ? htmlspecialchars($p['email']) : 'Sin correo'; ?></div>
                                            </td>

                                            <!-- FECHA DE NACIMIENTO -->
                                            <td data-label="Nacimiento">
                                                <span class="text-secondary small">
                                                    <i class="fa-solid fa-cake-candles me-1"></i> <?php echo !empty($p['fecha_nac']) ? htmlspecialchars($p['fecha_nac']) : 'No especificada'; ?>
                                                </span>
                                            </td>

                                            <!-- ALERGIAS (Con etiqueta destacada si tiene alguna) -->
                                            <td data-label="Alergias">
                                                <?php 
                                                    $alergias = trim($p['alergias'] ?? '');
                                                    $alergiasLower = mb_strtolower($alergias, 'UTF-8');
                                                    if (!empty($alergias) && $alergiasLower !== 'ninguna' && $alergiasLower !== 'ninguna registrada' && $alergiasLower !== '[null]'): 
                                                ?>
                                                    <span class="badge bg-danger bg-opacity-10 text-danger px-2 py-1" style="font-size: 0.75rem;">
                                                        <i class="fa-solid fa-triangle-exclamation me-1"></i> <?php echo htmlspecialchars($alergias); ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge bg-success bg-opacity-10 text-success px-2 py-1" style="font-size: 0.75rem;">
                                                        <i class="fa-solid fa-shield-halved me-1"></i> Ninguna
                                                    </span>
                                                <?php endif; ?>
                                            </td>

                                            <!-- ACCIONES (RF-042: Recepción solo puede depurar/eliminar pacientes sin historial; edición exclusiva del titular) -->
                                            <td data-label="Acciones" class="text-center">
                                                <div class="d-inline-flex gap-2 align-items-center justify-content-center">
                                                    <form action="../../app/logica_paciente.php" method="POST" style="margin: 0;" onsubmit="return confirm('¿Deseas eliminar a este paciente? Si tiene citas o historial clínico registrado no podrá eliminarse.');">
                                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
                                                        <input type="hidden" name="accion" value="eliminar">
                                                        <input type="hidden" name="id" value="<?php echo (int) $p['id']; ?>">
                                                        <button type="submit" class="action action--delete border-0">
                                                            <i class="fa-solid fa-trash-can"></i> Eliminar
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="7" class="text-center text-muted py-4">No hay pacientes registrados en este momento.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <footer class="app-footer">
                <div>&copy; 2026 DENTIX</div>
                <div>ADSO - SENA</div>
            </footer>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
    <script src="../../assets/js/admin-animations.js"></script>
    <script>
        (() => {
            const tbody = document.getElementById('tablaPacientesBody');
            const syncText = document.getElementById('liveSyncText');
            const syncBadge = document.getElementById('liveSyncBadge');
            if (!tbody) return;

            const csrfToken = <?php echo json_encode($csrfToken, JSON_UNESCAPED_UNICODE); ?>;
            let estadoPrevio = <?php echo json_encode($pacientes, JSON_UNESCAPED_UNICODE); ?>;
            const mapaPrevio = new Map(estadoPrevio.map((p) => [String(p.id), JSON.stringify(p)]));

            const esc = (v) => String(v ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');

            const renderAlergia = (raw) => {
                const val = String(raw ?? '').trim();
                const low = val.toLowerCase();
                if (val !== '' && low !== 'ninguna' && low !== 'ninguna registrada' && low !== '[null]') {
                    return `<span class="badge bg-danger bg-opacity-10 text-danger px-2 py-1" style="font-size: 0.75rem;"><i class="fa-solid fa-triangle-exclamation me-1"></i> ${esc(val)}</span>`;
                }
                return `<span class="badge bg-success bg-opacity-10 text-success px-2 py-1" style="font-size: 0.75rem;"><i class="fa-solid fa-shield-halved me-1"></i> Ninguna</span>`;
            };

            const renderFilas = (lista, cambiados) => {
                if (!Array.isArray(lista) || lista.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted py-4">No hay pacientes registrados en este momento.</td></tr>';
                    return;
                }
                tbody.innerHTML = lista.map((p, idx) => {
                    const idStr = String(p.id);
                    const flashClass = cambiados.has(idStr) ? ' class="dentix-row-updated"' : '';
                    return `
                        <tr data-paciente-id="${esc(idStr)}"${flashClass}>
                            <td data-label="#"><span class="row-id">${idx + 1}</span></td>
                            <td data-label="Nombre"><strong>${esc(p.nombre)}</strong></td>
                            <td data-label="Documento">
                                <span class="badge bg-light text-dark border px-2 py-1 fw-bold">
                                    <i class="fa-solid fa-id-card me-1 text-teal" style="color: #0d9488;"></i> ${p.cedula ? esc(p.cedula) : 'Sin registro'}
                                </span>
                            </td>
                            <td data-label="Contacto">
                                <div><i class="fa-solid fa-phone me-1 text-muted small"></i> ${esc(p.telefono)}</div>
                                <div class="text-muted small"><i class="fa-solid fa-envelope me-1"></i> ${p.email ? esc(p.email) : 'Sin correo'}</div>
                            </td>
                            <td data-label="Nacimiento">
                                <span class="text-secondary small">
                                    <i class="fa-solid fa-cake-candles me-1"></i> ${p.fecha_nac ? esc(p.fecha_nac) : 'No especificada'}
                                </span>
                            </td>
                            <td data-label="Alergias">${renderAlergia(p.alergias)}</td>
                            <td data-label="Acciones" class="text-center">
                                <div class="d-inline-flex gap-2 align-items-center justify-content-center">
                                    <form action="../../app/logica_paciente.php" method="POST" style="margin: 0;" onsubmit="return confirm('¿Deseas eliminar a este paciente? Si tiene citas o historial clínico registrado no podrá eliminarse.');">
                                        <input type="hidden" name="csrf_token" value="${esc(csrfToken)}">
                                        <input type="hidden" name="accion" value="eliminar">
                                        <input type="hidden" name="id" value="${esc(idStr)}">
                                        <button type="submit" class="action action--delete border-0">
                                            <i class="fa-solid fa-trash-can"></i> Eliminar
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>`;
                }).join('');
            };

            const sincronizarPacientes = async () => {
                if (document.visibilityState !== 'visible') return;
                try {
                    const res = await fetch('lista_pacientes.php?ajax_pacientes=1', {
                        credentials: 'same-origin',
                        cache: 'no-store'
                    });
                    if (!res.ok) return;
                    const data = await res.json();
                    if (!data || !data.ok || !Array.isArray(data.pacientes)) return;

                    const cambiados = new Set();
                    const nuevoMapa = new Map();
                    for (const p of data.pacientes) {
                        const key = String(p.id);
                        const sig = JSON.stringify(p);
                        nuevoMapa.set(key, sig);
                        if (!mapaPrevio.has(key) || mapaPrevio.get(key) !== sig) {
                            cambiados.add(key);
                        }
                    }

                    const huboEliminados = mapaPrevio.size !== nuevoMapa.size;
                    if (cambiados.size > 0 || huboEliminados) {
                        renderFilas(data.pacientes, cambiados);
                        mapaPrevio.clear();
                        for (const [k, v] of nuevoMapa.entries()) mapaPrevio.set(k, v);
                        estadoPrevio = data.pacientes;

                        if (syncText && syncBadge) {
                            syncText.textContent = 'Datos actualizados en vivo';
                            syncBadge.style.background = '#d1fae5';
                            setTimeout(() => {
                                syncText.textContent = 'Sincronización en vivo';
                                syncBadge.style.background = '#ecfdf5';
                            }, 3200);
                        }
                    }
                } catch (err) {
                    // Silencioso para no interrumpir la operación normal si hay pérdida momentánea de red
                }
            };

            setInterval(sincronizarPacientes, 3500);
        })();
    </script>
    <script src="../../assets/js/dentix-i18n.js?v=20261120-i18nfix"></script>
</body>
</html>