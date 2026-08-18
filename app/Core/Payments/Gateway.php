<?php
namespace App\Core\Payments;

/**
 * Payment gateway contract.
 *
 * Adding PayPal or a local provider later means writing one more class that
 * implements this interface and flipping PAYMENT_DRIVER - no checkout rewrite.
 */
interface Gateway
{
    /**
     * Start a payment for an order.
     *
     * @param array $order  Order row (id, reference, total, email)
     * @param array $items  Cart lines (product, quantity, unit)
     * @return array{redirect:?string, reference:string, status:string}
     */
    public function createCheckout(array $order, array $items, string $successUrl, string $cancelUrl): array;

    /**
     * Confirm a payment after the customer returns from the gateway.
     *
     * @return array{paid:bool, reference:?string, raw:array}
     */
    public function confirm(string $sessionReference): array;

    public function name(): string;
}
