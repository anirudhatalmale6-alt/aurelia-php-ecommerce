<?php
namespace App\Core;

/** Route guards. Each name maps to a check that either passes or redirects. */
class Middleware
{
    public static function handle(string $name): void
    {
        match ($name) {
            'auth'  => self::auth(),
            'admin' => self::admin(),
            'guest' => self::guest(),
            default => throw new \RuntimeException("Unknown middleware: $name"),
        };
    }

    private static function auth(): void
    {
        if (!Auth::check()) {
            $_SESSION['_intended'] = $_SERVER['REQUEST_URI'] ?? '/account';
            Flash::add('error', 'Please sign in to continue.');
            redirect('/login');
        }
    }

    private static function admin(): void
    {
        if (!Auth::isAdmin()) {
            http_response_code(403);
            View::render('errors/403', ['title' => 'Not authorised']);
            exit;
        }
    }

    private static function guest(): void
    {
        if (Auth::check()) {
            redirect('/account');
        }
    }
}
