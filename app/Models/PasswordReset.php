<?php
namespace App\Models;

use App\Core\Database;

/**
 * Password reset tokens.
 *
 * Only a SHA-256 hash of the token is stored, so a database leak does not hand
 * an attacker working reset links. Tokens are single-use and expire in 1 hour.
 */
class PasswordReset
{
    private const TTL = 3600;

    private static function db(): Database
    {
        return Database::instance();
    }

    /** Issue a token and return the plaintext half to email to the user. */
    public static function issue(string $email): string
    {
        $email = mb_strtolower(trim($email));
        self::db()->run('DELETE FROM password_resets WHERE email = :email', ['email' => $email]);

        $token = bin2hex(random_bytes(32));
        self::db()->insert('password_resets', [
            'email'      => $email,
            'token_hash' => hash('sha256', $token),
            'expires_at' => date('Y-m-d H:i:s', time() + self::TTL),
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        return $token;
    }

    /** Return the reset row if the token is valid and unexpired. */
    public static function verify(string $email, string $token): ?array
    {
        $row = self::db()->first(
            'SELECT * FROM password_resets WHERE email = :email',
            ['email' => mb_strtolower(trim($email))]
        );
        if (!$row) {
            return null;
        }
        if (strtotime($row['expires_at']) < time()) {
            self::consume($row['email']);
            return null;
        }
        return hash_equals($row['token_hash'], hash('sha256', $token)) ? $row : null;
    }

    public static function consume(string $email): void
    {
        self::db()->run('DELETE FROM password_resets WHERE email = :email', ['email' => $email]);
    }

    /** Housekeeping - call from a daily cron. */
    public static function pruneExpired(): void
    {
        self::db()->run('DELETE FROM password_resets WHERE expires_at < :now', ['now' => date('Y-m-d H:i:s')]);
    }
}
