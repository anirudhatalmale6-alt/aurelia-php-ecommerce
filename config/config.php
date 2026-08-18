<?php
/**
 * Application configuration.
 *
 * Values are read from the environment first (see .env.example) so the same
 * codebase can run in development, staging and production without edits.
 */

$root = dirname(__DIR__);

/** Tiny .env loader - keeps secrets out of version control. */
if (is_file($root . '/.env')) {
    foreach (file($root . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if ($line === '' || $line[0] === '#' || !str_contains($line, '=')) {
            continue;
        }
        [$k, $v] = explode('=', $line, 2);
        $_ENV[trim($k)] = trim($v, " \t\"'");
    }
}

/** Read an environment value with a fallback. */
function env(string $key, $default = null)
{
    return $_ENV[$key] ?? getenv($key) ?: $default;
}

return [
    'app' => [
        'name'     => env('APP_NAME', 'Aurelia'),
        'tagline'  => env('APP_TAGLINE', 'Considered goods for everyday life'),
        'url'      => env('APP_URL', 'http://localhost:8000'),
        'debug'    => filter_var(env('APP_DEBUG', 'true'), FILTER_VALIDATE_BOOL),
        'currency' => env('APP_CURRENCY', 'USD'),
        'symbol'   => env('APP_CURRENCY_SYMBOL', '$'),
        'root'     => $root,
    ],

    // Swap driver to 'mysql' in production. The query layer is written against
    // portable SQL so both drivers run the identical application code.
    'db' => [
        'driver'   => env('DB_DRIVER', 'sqlite'),
        'host'     => env('DB_HOST', '127.0.0.1'),
        'port'     => env('DB_PORT', '3306'),
        'database' => env('DB_DATABASE', 'aurelia'),
        'username' => env('DB_USERNAME', 'root'),
        'password' => env('DB_PASSWORD', ''),
        'sqlite'   => $root . '/storage/aurelia.sqlite',
    ],

    // 'stripe' uses the live Stripe API; 'demo' simulates an authorisation so
    // the storefront can be evaluated before keys are issued.
    'payments' => [
        'driver'         => env('PAYMENT_DRIVER', 'demo'),
        'stripe_secret'  => env('STRIPE_SECRET', ''),
        'stripe_public'  => env('STRIPE_PUBLISHABLE', ''),
        'stripe_webhook' => env('STRIPE_WEBHOOK_SECRET', ''),
    ],

    'mail' => [
        'driver' => env('MAIL_DRIVER', 'log'), // log | mail | smtp
        'from'   => env('MAIL_FROM', 'orders@example.test'),
        'name'   => env('MAIL_FROM_NAME', 'Aurelia Orders'),
    ],

    'uploads' => [
        'path'      => $root . '/storage/uploads',
        'url'       => '/media',
        'max_bytes' => 4 * 1024 * 1024,
        'mimes'     => ['image/jpeg', 'image/png', 'image/webp'],
    ],
];
