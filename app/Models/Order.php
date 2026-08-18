<?php
namespace App\Models;

use App\Core\Database;

class Order
{
    public const STATUSES = ['pending', 'paid', 'processing', 'shipped', 'completed', 'cancelled', 'refunded'];

    private static function db(): Database
    {
        return Database::instance();
    }

    /** Human-friendly, non-sequential reference shown to customers. */
    public static function reference(): string
    {
        return 'AUR-' . date('ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));
    }

    /**
     * Persist an order and its line items in a single transaction.
     * Line prices are snapshotted so later price changes never rewrite history.
     */
    public static function place(array $order, array $items): int
    {
        $db = self::db();
        $db->beginTransaction();
        try {
            $orderId = $db->insert('orders', $order);

            foreach ($items as $item) {
                $db->insert('order_items', [
                    'order_id'     => $orderId,
                    'product_id'   => $item['product']['id'],
                    'product_name' => $item['product']['name'],
                    'sku'          => $item['product']['sku'],
                    'unit_price'   => $item['unit'],
                    'quantity'     => $item['quantity'],
                    'line_total'   => $item['subtotal'],
                ]);
            }
            $db->commit();
            return $orderId;
        } catch (\Throwable $e) {
            $db->rollBack();
            throw $e;
        }
    }

    public static function find(int $id): ?array
    {
        return self::db()->first('SELECT * FROM orders WHERE id = :id', ['id' => $id]);
    }

    public static function findByReference(string $reference): ?array
    {
        return self::db()->first('SELECT * FROM orders WHERE reference = :r', ['r' => $reference]);
    }

    public static function items(int $orderId): array
    {
        return self::db()->all(
            'SELECT * FROM order_items WHERE order_id = :id ORDER BY id ASC',
            ['id' => $orderId]
        );
    }

    public static function forUser(int $userId): array
    {
        return self::db()->all(
            'SELECT o.*, (
                 SELECT COUNT(*) FROM order_items i WHERE i.order_id = o.id
             ) AS item_count
               FROM orders o
              WHERE o.user_id = :id
           ORDER BY o.id DESC',
            ['id' => $userId]
        );
    }

    /** Admin listing with optional status filter and keyword search. */
    public static function adminList(string $status = '', string $search = ''): array
    {
        $where  = [];
        $params = [];
        if ($status !== '') {
            $where[]           = 'o.status = :status';
            $params['status']  = $status;
        }
        if ($search !== '') {
            $where[]     = '(LOWER(o.reference) LIKE :q OR LOWER(o.email) LIKE :q OR LOWER(o.customer_name) LIKE :q)';
            $params['q'] = '%' . mb_strtolower($search) . '%';
        }
        $clause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        return self::db()->all(
            "SELECT o.*, (
                 SELECT COUNT(*) FROM order_items i WHERE i.order_id = o.id
             ) AS item_count
               FROM orders o
             $clause
           ORDER BY o.id DESC",
            $params
        );
    }

    public static function setStatus(int $id, string $status): void
    {
        self::db()->update('orders', [
            'status'     => $status,
            'updated_at' => date('Y-m-d H:i:s'),
        ], $id);
    }

    /** Mark paid and draw the stock down. Idempotent - safe to call twice. */
    public static function markPaid(int $id, string $paymentReference): bool
    {
        $order = self::find($id);
        if (!$order || $order['status'] !== 'pending') {
            return false;
        }

        self::db()->update('orders', [
            'status'            => 'paid',
            'payment_reference' => $paymentReference,
            'paid_at'           => date('Y-m-d H:i:s'),
            'updated_at'        => date('Y-m-d H:i:s'),
        ], $id);

        foreach (self::items($id) as $item) {
            if ($item['product_id']) {
                Product::decrementStock((int) $item['product_id'], (int) $item['quantity']);
            }
        }
        return true;
    }

    /** Dashboard tiles. */
    public static function stats(): array
    {
        $db = self::db();
        return [
            'orders_total'   => (int) $db->scalar('SELECT COUNT(*) FROM orders'),
            'orders_pending' => (int) $db->scalar("SELECT COUNT(*) FROM orders WHERE status = 'pending'"),
            'revenue'        => (float) $db->scalar("SELECT COALESCE(SUM(total), 0) FROM orders WHERE status <> 'cancelled' AND status <> 'pending'"),
            'customers'      => (int) $db->scalar("SELECT COUNT(*) FROM users WHERE role = 'customer'"),
            'products'       => (int) $db->scalar('SELECT COUNT(*) FROM products WHERE is_active = 1'),
            'low_stock'      => (int) $db->scalar('SELECT COUNT(*) FROM products WHERE stock <= 5 AND is_active = 1'),
        ];
    }

    /** Revenue per day for the dashboard sparkline. */
    public static function revenueSeries(int $days = 14): array
    {
        $rows = self::db()->all(
            "SELECT substr(paid_at, 1, 10) AS day, SUM(total) AS amount
               FROM orders
              WHERE paid_at IS NOT NULL
           GROUP BY substr(paid_at, 1, 10)
           ORDER BY day DESC
              LIMIT " . (int) $days
        );
        $byDay = array_column($rows, 'amount', 'day');

        $series = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $day = date('Y-m-d', strtotime("-$i days"));
            $series[] = ['day' => $day, 'amount' => (float) ($byDay[$day] ?? 0)];
        }
        return $series;
    }
}
