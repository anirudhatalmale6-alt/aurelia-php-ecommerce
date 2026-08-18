<?php
namespace App\Core;

/** Per-session CSRF token, verified on every state-changing request. */
class Csrf
{
    public static function token(): string
    {
        if (empty($_SESSION['_csrf'])) {
            $_SESSION['_csrf'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['_csrf'];
    }

    public static function check(?string $token): bool
    {
        return is_string($token)
            && !empty($_SESSION['_csrf'])
            && hash_equals($_SESSION['_csrf'], $token);
    }

    /** Abort the request with 419 when the token is missing or stale. */
    public static function verify(): void
    {
        if (!self::check($_POST['_token'] ?? null)) {
            http_response_code(419);
            View::render('errors/419', ['title' => 'Session expired']);
            exit;
        }
    }
}
