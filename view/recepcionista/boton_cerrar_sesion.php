<div class="sidebar__footer">
    <div class="sidebar__session">
        <span class="sidebar__session-indicator" aria-hidden="true"></span>
        <span>Sesión de recepción activa</span>
    </div>
    <form action="../recepcionista/cerrar_sesion_recepcionista.php" method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(token_csrf_recepcionista(), ENT_QUOTES, 'UTF-8'); ?>">
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
