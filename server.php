<?php
/**
 * Router for PHP's built-in web server (development only).
 *
 *   php -S 127.0.0.1:8000 -t public server.php
 *
 * Static files under public/ are served directly; everything else is handed to
 * the front controller, which mirrors the Apache/nginx rewrite rules.
 */

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$file = __DIR__ . '/public' . $path;

if ($path !== '/' && is_file($file)) {
    return false;
}

require __DIR__ . '/public/index.php';
