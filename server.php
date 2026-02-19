<?php

/**
 * Shared hosting / PHP built-in server bootstrap.
 *
 * Supports:
 * - `php -S 127.0.0.1:8000 server.php`
 * - cPanel/shared hosting setups that point to project root
 */

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/');
$publicPath = __DIR__ . '/public';
$requestedFile = realpath($publicPath . $uri);

if ($uri !== '/' && $requestedFile && str_starts_with($requestedFile, realpath($publicPath)) && is_file($requestedFile)) {
    return false;
}

require_once $publicPath . '/index.php';
