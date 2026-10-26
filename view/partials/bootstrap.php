<?php

if (defined('SCO_BOOTSTRAP')) {
    return;
}

define('SCO_BOOTSTRAP', true);

$documentRoot = str_replace('\\', '/', rtrim($_SERVER['DOCUMENT_ROOT'] ?? '', '/'));
$projectRoot = str_replace('\\', '/', dirname(__DIR__, 2));
$basePath = '';

if ($documentRoot !== '' && str_starts_with($projectRoot, $documentRoot)) {
    $basePath = substr($projectRoot, strlen($documentRoot));
}

$basePath = rtrim($basePath, '/');

define('SCO_BASE_URL', $basePath);
define('SCO_ASSETS', SCO_BASE_URL . '/assests');
define('SCO_PROJECT_IMAGE_FILE', $projectRoot . '/assests/img/proyecto.jpg');
define('SCO_PROJECT_IMAGE_URL', SCO_ASSETS . '/img/proyecto.jpg');
define('SCO_PROJECT_PLACEHOLDER', SCO_ASSETS . '/img/proyecto-placeholder.svg');

if (!function_exists('sco_e')) {
    function sco_e($value): string
    {
        return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('sco_url')) {
    function sco_url(string $path = ''): string
    {
        return SCO_BASE_URL . '/' . ltrim($path, '/');
    }
}

if (!function_exists('sco_href')) {
    function sco_href(string $path = ''): string
    {
        return sco_e(sco_url($path));
    }
}

if (!function_exists('sco_asset')) {
    function sco_asset(string $path = ''): string
    {
        return SCO_ASSETS . '/' . ltrim($path, '/');
    }
}

if (!function_exists('sco_asset_href')) {
    function sco_asset_href(string $path = ''): string
    {
        return sco_e(sco_asset($path));
    }
}

if (!function_exists('sco_active')) {
    function sco_active(string $key, string $current): string
    {
        return $key === $current ? ' is-active' : '';
    }
}

if (!function_exists('sco_initials')) {
    function sco_initials(string $name): string
    {
        $name = trim($name);

        if ($name === '') {
            return '?';
        }

        $parts = preg_split('/\s+/u', $name) ?: [$name];
        $first = function_exists('mb_substr')
            ? mb_substr($parts[0], 0, 1, 'UTF-8')
            : substr($parts[0], 0, 1);

        $lastIndex = count($parts) - 1;
        $second = '';

        if ($lastIndex > 0) {
            $second = function_exists('mb_substr')
                ? mb_substr($parts[$lastIndex], 0, 1, 'UTF-8')
                : substr($parts[$lastIndex], 0, 1);
        }

        $initials = $first . $second;

        return function_exists('mb_strtoupper')
            ? mb_strtoupper($initials, 'UTF-8')
            : strtoupper($initials);
    }
}
