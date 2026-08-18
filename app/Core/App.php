<?php
namespace App\Core;

/**
 * Application container: holds configuration and boots the request lifecycle.
 */
class App
{
    private static array $config = [];

    public static function boot(array $config): void
    {
        self::$config = $config;

        if (self::$config['app']['debug']) {
            ini_set('display_errors', '1');
            error_reporting(E_ALL);
        } else {
            ini_set('display_errors', '0');
        }

        // Session cookie hardening. 'secure' is enabled automatically on HTTPS.
        session_set_cookie_params([
            'httponly' => true,
            'samesite' => 'Lax',
            'secure'   => (($_SERVER['HTTPS'] ?? '') === 'on'),
        ]);
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /** Dot-notation config access: App::config('app.name'). */
    public static function config(string $key, $default = null)
    {
        $value = self::$config;
        foreach (explode('.', $key) as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return $default;
            }
            $value = $value[$segment];
        }
        return $value;
    }

    public static function currency(float $amount): string
    {
        return self::config('app.symbol') . number_format($amount, 2);
    }

    public static function url(string $path = ''): string
    {
        return rtrim(self::config('app.url'), '/') . '/' . ltrim($path, '/');
    }
}
