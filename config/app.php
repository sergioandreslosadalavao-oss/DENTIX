<?php

declare(strict_types=1);

define('APP_NAME', 'DENTIX');
define('APP_ENV', getenv('APP_ENV') ?: 'local');

define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');
define('VIEW_PATH', APP_PATH . '/views');
define('CONFIG_PATH', BASE_PATH . '/config');
define('PUBLIC_PATH', BASE_PATH . '/public');

if (!defined('BASE_URL')) {
    $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/'));
    $scriptDir = rtrim($scriptDir, '/');
    define('BASE_URL', $scriptDir === '/' ? '' : $scriptDir);
}
