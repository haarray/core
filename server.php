<?php

/**
 * Shared hosting / PHP built-in server bootstrap.
 *
 * Supports:
 * - php -S 127.0.0.1:8000 server.php
 * - Root path access: /, /dashboard, ...
 * - Sub-directory style access: /harray-core, /haaray-core, /haaray
 */

$requestUri = (string) ($_SERVER['REQUEST_URI'] ?? '/');
$path = urldecode((string) (parse_url($requestUri, PHP_URL_PATH) ?? '/'));
$query = (string) (parse_url($requestUri, PHP_URL_QUERY) ?? '');
$publicPath = __DIR__ . '/public';
$publicReal = realpath($publicPath) ?: $publicPath;

$candidates = array_values(array_unique(array_filter([
    '/' . trim((string) basename(__DIR__), '/'),
    '/harray-core',
    '/haaray-core',
    '/haaray',
])));

$basePath = '';
foreach ($candidates as $candidate) {
    if ($path === $candidate || str_starts_with($path, $candidate . '/')) {
        $basePath = $candidate;
        break;
    }
}

if ($basePath !== '') {
    $path = substr($path, strlen($basePath)) ?: '/';
}

$resolved = realpath($publicPath . $path);
if ($path !== '/' && $resolved && str_starts_with($resolved, $publicReal) && is_file($resolved)) {
    $mime = function_exists('mime_content_type') ? (mime_content_type($resolved) ?: '') : '';
    if ($mime !== '') {
        header('Content-Type: ' . $mime);
    }
    header('Content-Length: ' . (string) filesize($resolved));
    readfile($resolved);
    exit;
}

if ($basePath !== '') {
    $_SERVER['SCRIPT_NAME'] = $basePath . '/index.php';
    $_SERVER['PHP_SELF'] = $_SERVER['SCRIPT_NAME'];
    $_SERVER['REQUEST_URI'] = $path . ($query !== '' ? '?' . $query : '');
}

require_once $publicPath . '/index.php';
