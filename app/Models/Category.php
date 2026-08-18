<?php
namespace App\Models;

use App\Core\Database;

class Category
{
    private static function db(): Database
    {
        return Database::instance();
    }

    /** All categories with a live product count, for nav and filters. */
    public static function all(): array
    {
        return self::db()->all(
            'SELECT c.*, (
                 SELECT COUNT(*) FROM products p
                  WHERE p.category_id = c.id AND p.is_active = 1
             ) AS product_count
               FROM categories c
           ORDER BY c.position ASC, c.name ASC'
        );
    }

    public static function find(int $id): ?array
    {
        return self::db()->first('SELECT * FROM categories WHERE id = :id', ['id' => $id]);
    }

    public static function findBySlug(string $slug): ?array
    {
        return self::db()->first('SELECT * FROM categories WHERE slug = :slug', ['slug' => $slug]);
    }

    public static function create(array $data): int
    {
        return self::db()->insert('categories', $data);
    }

    public static function update(int $id, array $data): void
    {
        self::db()->update('categories', $data, $id);
    }

    public static function delete(int $id): void
    {
        // Products keep existing but become uncategorised.
        self::db()->run('UPDATE products SET category_id = NULL WHERE category_id = :id', ['id' => $id]);
        self::db()->delete('categories', $id);
    }

    public static function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = trim(preg_replace('/[^a-z0-9]+/', '-', mb_strtolower($name)), '-') ?: 'category';
        $slug = $base;
        $i    = 2;
        while (true) {
            $sql    = 'SELECT COUNT(*) FROM categories WHERE slug = :slug';
            $params = ['slug' => $slug];
            if ($ignoreId) {
                $sql .= ' AND id <> :id';
                $params['id'] = $ignoreId;
            }
            if ((int) self::db()->scalar($sql, $params) === 0) {
                return $slug;
            }
            $slug = $base . '-' . $i++;
        }
    }
}
