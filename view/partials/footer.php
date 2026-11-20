<?php

require_once __DIR__ . '/bootstrap.php';

?>
            </main>

            <footer class="app-footer">
                <p>© <?= date('Y') ?> DENTIX · Sistema de Citas Odontológicas</p>
                <a href="<?= sco_href('index.php') ?>">Volver al inicio</a>
            </footer>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.13.0/gsap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.13.0/ScrollTrigger.min.js"></script>
    <script src="<?= sco_asset_href('js/admin-animations.js') ?>"></script>
</body>
</html>
