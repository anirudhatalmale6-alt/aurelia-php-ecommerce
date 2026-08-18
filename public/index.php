<?php
/**
 * Front controller. Every request enters here.
 *
 * Point your document root at this directory - app/, config/ and storage/ then
 * sit outside the web root and cannot be requested directly.
 */

declare(strict_types=1);

$root = dirname(__DIR__);

/** PSR-4 style autoloader for the App\ namespace. */
spl_autoload_register(function (string $class) use ($root): void {
    if (!str_starts_with($class, 'App\\')) {
        return;
    }
    $path = $root . '/app/' . str_replace('\\', '/', substr($class, 4)) . '.php';
    if (is_file($path)) {
        require $path;
    }
});

$config = require $root . '/config/config.php';

require $root . '/app/Support/helpers.php';

use App\Core\App;
use App\Core\Flash;
use App\Core\Request;

App::boot($config);
Flash::shift();

// Baseline security headers.
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('Referrer-Policy: strict-origin-when-cross-origin');

$router  = require $root . '/config/routes.php';
$request = new Request();

try {
    $router->dispatch($request);
} catch (\Throwable $e) {
    if (App::config('app.debug')) {
        throw $e;
    }
    error_log('[' . date('c') . '] ' . $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine());
    http_response_code(500);
    App\Core\View::render('errors/500', ['title' => 'Something went wrong']);
}
