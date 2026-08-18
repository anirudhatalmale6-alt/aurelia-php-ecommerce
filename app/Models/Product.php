<?php
namespace App\Models;

use App\Core\Database;

/** Catalogue queries. */
class Product
{
    public static function db(): Database
    {
        return Database::instance();
    }

    public static function find(int $id): ?array
    {
        return self::db()->first(
            'SELECT p.*, c.name AS category_name, c.slug AS category_slug
               FROM products p
          LEFT JOIN categories c ON c.id = p.category_id
              WHERE p.id = :id',
            ['id' => $id]
        );
    }

    public static function findBySlug(string $slug): ?array
    {
        return self::db()->first(
            'SELECT p.*, c.name AS category_name, c.slug AS category_slug
               FROM products p
          LEFT JOIN categories c ON c.id = p.category_id
              WHERE p.slug = :slug AND p.is_active = 1',
            ['slug' => $slug]
        );
    }

    /** @param int[] $ids */
    public static function findMany(array $ids): array
    {
        $ids = array_values(array_unique(array_filter($ids)));
        if (!$ids) {
            return [];
        }
        $in = implode(',', array_fill(0, count($ids), '?'));
        return self::db()->all("SELECT * FROM products WHERE id IN ($in)", $ids);
    }

    /**
     * Storefront listing with optional category filter, keyword search and sort.
     *
     * @return array{rows: array, total: int, pages: int}
     */
    public static function browse(array $filters = [], int $page = 1, int $perPage = 12): array
    {
        $where  = ['p.is_active = 1'];
        $params = [];

        if (!empty($filters['category'])) {
            $where[]             = 'c.slug = :category';
            $params['category']  = $filters['category'];
        }
        if (!empty($filters['q'])) {
            $where[]        = '(LOWER(p.name) LIKE :q OR LOWER(p.description) LIKE :q OR LOWER(p.sku) LIKE :q)';
            $params['q']    = '%' . mb_strtolower($filters['q']) . '%';
        }
        if (!empty($filters['max_price'])) {
            $where[]              = 'COALESCE(NULLIF(p.sale_price, 0), p.price) <= :max_price';
            $params['max_price']  = (float) $filters['max_price'];
        }
        if (!empty($filters['in_stock'])) {
            $where[] = 'p.stock > 0';
        }

        $order = match ($filters['sort'] ?? '') {
            'price_asc'  => 'COALESCE(NULLIF(p.sale_price, 0), p.price) ASC',
            'price_desc' => 'COALESCE(NULLIF(p.sale_price, 0), p.price) DESC',
            'name'       => 'p.name ASC',
            default      => 'p.created_at DESC, p.id DESC',
        };

        $clause = 'WHERE ' . implode(' AND ', $where);

        $total = (int) self::db()->scalar(
            "SELECT COUNT(*) FROM products p LEFT JOIN categories c ON c.id = p.category_id $clause",
            $params
        );

        $page    = max(1, $page);
        $offset  = ($page - 1) * $perPage;
        $rows    = self::db()->all(
            "SELECT p.*, c.name AS category_name, c.slug AS category_slug
               FROM products p
          LEFT JOIN categories c ON c.id = p.category_id
             $clause
           ORDER BY $order
              LIMIT $perPage OFFSET $offset",
            $params
        );

        return [
            'rows'  => $rows,
            'total' => $total,
            'pages' => max(1, (int) ceil($total / $perPage)),
            'page'  => $page,
        ];
    }

    public static function featured(int $limit = 8): array
    {
        return self::db()->all(
            'SELECT p.*, c.name AS category_name, c.slug AS category_slug
               FROM products p
          LEFT JOIN categories c ON c.id = p.category_id
              WHERE p.is_active = 1 AND p.is_featured = 1
           ORDER BY p.id DESC
              LIMIT ' . (int) $limit
        );
    }

    public static function related(array $product, int $limit = 4): array
    {
        return self::db()->all(
            'SELECT * FROM products
              WHERE is_active = 1 AND category_id = :cat AND id <> :id
           ORDER BY id DESC LIMIT ' . (int) $limit,
            ['cat' => $product['category_id'], 'id' => $product['id']]
        );
    }

    /** Admin listing - includes inactive products. */
    public static function adminList(string $search = ''): array
    {
        $params = [];
        $clause = '';
        if ($search !== '') {
            $clause      = 'WHERE LOWER(p.name) LIKE :q OR LOWER(p.sku) LIKE :q';
            $params['q'] = '%' . mb_strtolower($search) . '%';
        }
        return self::db()->all(
            "SELECT p.*, c.name AS category_name
               FROM products p
          LEFT JOIN categories c ON c.id = p.category_id
             $clause
           ORDER BY p.id DESC",
            $params
        );
    }

    public static function create(array $data): int
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = $data['created_at'];
        return self::db()->insert('products', $data);
    }

    public static function update(int $id, array $data): void
    {
        $data['updated_at'] = date('Y-m-d H:i:s');
        self::db()->update('products', $data, $id);
    }

    public static function delete(int $id): void
    {
        self::db()->delete('products', $id);
    }

    /** Decrement stock when an order is paid. */
    public static function decrementStock(int $id, int $quantity): void
    {
        self::db()->run(
            'UPDATE products SET stock = CASE WHEN stock >= :q THEN stock - :q ELSE 0 END WHERE id = :id',
            ['q' => $quantity, 'id' => $id]
        );
    }

    /** Unique, URL-safe slug derived from the product name. */
    public static function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = trim(preg_replace('/[^a-z0-9]+/', '-', mb_strtolower($name)), '-') ?: 'product';
        $slug = $base;
        $i    = 2;
        while (true) {
            $sql    = 'SELECT COUNT(*) FROM products WHERE slug = :slug';
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

    /** Effective selling price (sale price when set). */
    public static function price(array $product): float
    {
        return (float) ($product['sale_price'] ?: $product['price']);
    }
}
