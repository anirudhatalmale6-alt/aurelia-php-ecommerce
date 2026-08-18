<?php
namespace App\Core;

use App\Models\Product;

/**
 * Session cart.
 *
 * Only product ids and quantities are stored in the session - prices are always
 * re-read from the database when the cart is rendered or an order is placed, so
 * a tampered session can never change what a customer is charged.
 */
class Cart
{
    private const KEY = '_cart';

    public static function items(): array
    {
        $lines = $_SESSION[self::KEY] ?? [];
        if (!$lines) {
            return [];
        }

        $products = Product::findMany(array_map('intval', array_keys($lines)));
        $items    = [];

        foreach ($products as $product) {
            $qty = (int) ($lines[$product['id']] ?? 0);
            if ($qty < 1 || $product['is_active'] != 1) {
                continue;
            }
            // Never sell more than is on hand.
            $qty = min($qty, max(0, (int) $product['stock']));
            if ($qty === 0) {
                continue;
            }
            $price = (float) ($product['sale_price'] ?: $product['price']);
            $items[] = [
                'product'  => $product,
                'quantity' => $qty,
                'unit'     => $price,
                'subtotal' => round($price * $qty, 2),
            ];
        }
        return $items;
    }

    public static function add(int $productId, int $quantity = 1): void
    {
        $quantity = max(1, $quantity);
        $_SESSION[self::KEY][$productId] = ($_SESSION[self::KEY][$productId] ?? 0) + $quantity;
    }

    public static function set(int $productId, int $quantity): void
    {
        if ($quantity < 1) {
            self::remove($productId);
            return;
        }
        $_SESSION[self::KEY][$productId] = $quantity;
    }

    public static function remove(int $productId): void
    {
        unset($_SESSION[self::KEY][$productId]);
    }

    public static function clear(): void
    {
        unset($_SESSION[self::KEY]);
    }

    public static function count(): int
    {
        return array_sum(array_map(fn($i) => $i['quantity'], self::items()));
    }

    public static function subtotal(): float
    {
        return round(array_sum(array_map(fn($i) => $i['subtotal'], self::items())), 2);
    }

    /** Flat-rate shipping, free above a threshold. Swap for a rules table later. */
    public static function shipping(): float
    {
        $subtotal = self::subtotal();
        if ($subtotal <= 0 || $subtotal >= 75) {
            return 0.0;
        }
        return 6.95;
    }

    public static function tax(): float
    {
        return round(self::subtotal() * 0.0, 2); // configurable per-region later
    }

    public static function total(): float
    {
        return round(self::subtotal() + self::shipping() + self::tax(), 2);
    }

    public static function summary(): array
    {
        return [
            'items'    => self::items(),
            'subtotal' => self::subtotal(),
            'shipping' => self::shipping(),
            'tax'      => self::tax(),
            'total'    => self::total(),
        ];
    }
}
