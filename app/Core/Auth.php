<?php
namespace App\Core;

use App\Models\User;

/**
 * Session-based authentication.
 *
 * Passwords are hashed with PASSWORD_DEFAULT (bcrypt/argon2 depending on the
 * PHP build) and re-hashed transparently when the algorithm cost changes.
 */
class Auth
{
    private static ?array $cached = null;

    public static function attempt(string $email, string $password): bool
    {
        $user = User::findByEmail($email);
        if (!$user || !password_verify($password, $user['password_hash'])) {
            return false;
        }

        if (password_needs_rehash($user['password_hash'], PASSWORD_DEFAULT)) {
            User::updatePassword((int) $user['id'], $password);
        }

        self::login($user);
        return true;
    }

    public static function login(array $user): void
    {
        // New session id on privilege change - blocks session fixation.
        session_regenerate_id(true);
        $_SESSION['user_id'] = (int) $user['id'];
        self::$cached = $user;
    }

    public static function logout(): void
    {
        unset($_SESSION['user_id']);
        self::$cached = null;
        session_regenerate_id(true);
    }

    public static function check(): bool
    {
        return self::user() !== null;
    }

    public static function isAdmin(): bool
    {
        $u = self::user();
        return $u !== null && $u['role'] === 'admin';
    }

    public static function id(): ?int
    {
        return isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
    }

    public static function user(): ?array
    {
        if (self::$cached !== null) {
            return self::$cached;
        }
        if (!isset($_SESSION['user_id'])) {
            return null;
        }
        $user = User::find((int) $_SESSION['user_id']);
        if (!$user) {
            unset($_SESSION['user_id']);
            return null;
        }
        return self::$cached = $user;
    }

    /**
     * Simple per-session throttle for login and password-reset forms.
     * Returns false once the attempt budget for the window is exhausted.
     */
    public static function throttle(string $key, int $max = 5, int $seconds = 300): bool
    {
        $now  = time();
        $slot = $_SESSION['_throttle'][$key] ?? ['count' => 0, 'until' => $now + $seconds];

        if ($now > $slot['until']) {
            $slot = ['count' => 0, 'until' => $now + $seconds];
        }
        $slot['count']++;
        $_SESSION['_throttle'][$key] = $slot;

        return $slot['count'] <= $max;
    }
}
