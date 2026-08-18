<?php
namespace App\Models;

use App\Core\Database;

class User
{
    private static function db(): Database
    {
        return Database::instance();
    }

    public static function find(int $id): ?array
    {
        return self::db()->first('SELECT * FROM users WHERE id = :id', ['id' => $id]);
    }

    public static function findByEmail(string $email): ?array
    {
        return self::db()->first(
            'SELECT * FROM users WHERE email = :email',
            ['email' => mb_strtolower(trim($email))]
        );
    }

    public static function emailTaken(string $email, ?int $ignoreId = null): bool
    {
        $sql    = 'SELECT COUNT(*) FROM users WHERE email = :email';
        $params = ['email' => mb_strtolower(trim($email))];
        if ($ignoreId) {
            $sql .= ' AND id <> :id';
            $params['id'] = $ignoreId;
        }
        return (int) self::db()->scalar($sql, $params) > 0;
    }

    public static function create(string $name, string $email, string $password, string $role = 'customer'): int
    {
        return self::db()->insert('users', [
            'name'          => $name,
            'email'         => mb_strtolower(trim($email)),
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'role'          => $role,
            'created_at'    => date('Y-m-d H:i:s'),
        ]);
    }

    public static function updateProfile(int $id, array $data): void
    {
        self::db()->update('users', $data, $id);
    }

    public static function updatePassword(int $id, string $password): void
    {
        self::db()->update('users', [
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
        ], $id);
    }

    public static function all(): array
    {
        return self::db()->all(
            'SELECT u.*, (
                SELECT COUNT(*) FROM orders o WHERE o.user_id = u.id
             ) AS order_count
               FROM users u ORDER BY u.id DESC'
        );
    }
}
