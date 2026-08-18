<?php
namespace App\Core\Payments;

/**
 * Offline gateway used while Stripe keys are pending.
 *
 * It renders a hosted-checkout lookalike page inside the app so the full order
 * flow (place -> pay -> confirm -> email -> order history) can be demonstrated
 * end to end. Switching PAYMENT_DRIVER to 'stripe' changes nothing else.
 */
class DemoGateway implements Gateway
{
    public function name(): string
    {
        return 'Demo (sandbox)';
    }

    public function createCheckout(array $order, array $items, string $successUrl, string $cancelUrl): array
    {
        $reference = 'demo_' . bin2hex(random_bytes(10));

        $_SESSION['_demo_payments'][$reference] = [
            'order_id' => $order['id'],
            'amount'   => $order['total'],
            'paid'     => false,
        ];

        $query = http_build_query([
            'session' => $reference,
            'success' => $successUrl,
            'cancel'  => $cancelUrl,
        ]);

        return [
            'redirect'  => '/checkout/sandbox?' . $query,
            'reference' => $reference,
            'status'    => 'pending',
        ];
    }

    /** Called by the sandbox page when the customer presses "Pay". */
    public function markPaid(string $reference): void
    {
        if (isset($_SESSION['_demo_payments'][$reference])) {
            $_SESSION['_demo_payments'][$reference]['paid'] = true;
        }
    }

    public function confirm(string $sessionReference): array
    {
        $record = $_SESSION['_demo_payments'][$sessionReference] ?? null;
        return [
            'paid'      => (bool) ($record['paid'] ?? false),
            'reference' => $sessionReference,
            'raw'       => $record ?? [],
        ];
    }
}
