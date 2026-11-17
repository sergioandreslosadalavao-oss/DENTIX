<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/config/app.php';
require_once CONFIG_PATH . '/database.php';
require_once APP_PATH . '/helpers/url.php';
require_once APP_PATH . '/helpers/view.php';
require_once APP_PATH . '/helpers/flash.php';
require_once APP_PATH . '/helpers/auth.php';

iniciar_sesion();
