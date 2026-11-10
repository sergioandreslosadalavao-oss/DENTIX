<?php
$nombreRecepcionista = $_SESSION['recepcionista_nombre'] ?? 'Recepción';
$inicialRecepcionista = mb_strtoupper(mb_substr($nombreRecepcionista, 0, 1, 'UTF-8'), 'UTF-8');
$csrfTokenRecep = function_exists('token_csrf_recepcionista') ? token_csrf_recepcionista() : ($_SESSION['csrf_recepcionista'] ?? '');
?>
<div class="sidebar__footer">
    <!-- Tarjeta de Perfil del Recepcionista estilizada DENTIX -->
    <div class="sidebar-user-card" style="
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 14px;
        margin-bottom: 14px;
        border-radius: 16px;
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.1) 0%, rgba(255, 255, 255, 0.04) 100%);
        border: 1px solid rgba(255, 255, 255, 0.16);
        backdrop-filter: blur(10px);
        box-shadow: 0 4px 16px -2px rgba(0, 0, 0, 0.25);
    ">
        <!-- Avatar circular con glow e inicial -->
        <div style="
            position: relative;
            width: 40px;
            height: 40px;
            flex-shrink: 0;
            border-radius: 12px;
            background: linear-gradient(135deg, #10b981 0%, #0d9488 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            font-weight: 800;
            font-size: 1rem;
            font-family: 'Sora', sans-serif;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4);
            border: 1px solid rgba(255, 255, 255, 0.3);
        ">
            <?php echo htmlspecialchars($inicialRecepcionista, ENT_QUOTES, 'UTF-8'); ?>
            <span style="
                position: absolute;
                bottom: -2px;
                right: -2px;
                width: 10px;
                height: 10px;
                border-radius: 50%;
                background: #10b981;
                border: 2px solid #064e3b;
                box-shadow: 0 0 6px #10b981;
            "></span>
        </div>

        <!-- Info del Recepcionista -->
        <div style="min-width: 0; flex: 1; line-height: 1.2;">
            <div style="
                font-family: 'Manrope', sans-serif;
                font-weight: 700;
                font-size: 0.88rem;
                color: #ffffff;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
                letter-spacing: -0.01em;
            ">
                <?php echo htmlspecialchars($nombreRecepcionista, ENT_QUOTES, 'UTF-8'); ?>
            </div>
            <div style="
                display: flex;
                align-items: center;
                gap: 5px;
                margin-top: 3px;
                font-size: 0.7rem;
                font-weight: 600;
                color: #a7f3d0;
            ">
                <i class="fa-solid fa-shield-halved" style="font-size: 0.65rem;"></i>
                <span>Recepcionista Activo</span>
            </div>
        </div>
    </div>

    <!-- Botón de Cerrar Sesión oficial -->
    <form action="../recepcionista/cerrar_sesion_recepcionista.php" method="POST" style="margin: 0;">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfTokenRecep, ENT_QUOTES, 'UTF-8'); ?>">
        <button class="sidebar__logout" type="submit">
            <span class="sidebar__logout-icon" aria-hidden="true">
                <i class="fa-solid fa-arrow-right-from-bracket"></i>
            </span>
            <span class="sidebar__logout-copy">
                <strong>Cerrar sesión</strong>
                <small>Salir de forma segura</small>
            </span>
            <span class="sidebar__logout-arrow" aria-hidden="true">
                <i class="fa-solid fa-arrow-right"></i>
            </span>
        </button>
    </form>
</div>
