<?php
namespace App\Core\Payments;

use App\Core\App;

/**
 * Stripe Checkout integration over the raw REST API (no Composer dependency).
 *
 * Flow: create a Checkout Session -> redirect the customer to Stripe -> on
 * return, retrieve the session and treat payment_status === 'paid' as settled.
 * The webhook endpoint (POST /webhooks/stripe) is the authoritative confirmation
 * for cards that settle asynchronously.
 */
class StripeGateway implements Gateway
{
    private string $secret;

    public function __construct()
    {
        $this->secret = (string) App::config('payments.stripe_secret');
        if ($this->secret === '') {
            throw new \RuntimeException('STRIPE_SECRET is not configured.');
        }
    }

    public function name(): string
    {
        return 'Stripe';
    }

    public function createCheckout(array $order, array $items, string $successUrl, string $cancelUrl): array
    {
        $currency = strtolower(App::config('app.currency'));
        $payload  = [
            'mode'                 => 'payment',
            'success_url'          => $successUrl . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url'           => $cancelUrl,
            'client_reference_id'  => $order['reference'],
            'customer_email'       => $order['email'],
            'metadata' => [
                'order_id'        => (string) $order['id'],
                'order_reference' => $order['reference'],
            ],
        ];

        $line = 0;
        foreach ($items as $item) {
            $payload["line_items[$line][price_data][currency]"]              = $currency;
            $payload["line_items[$line][price_data][unit_amount]"]           = (int) round($item['unit'] * 100);
            $payload["line_items[$line][price_data][product_data][name]"]    = $item['product']['name'];
            $payload["line_items[$line][quantity]"]                          = $item['quantity'];
            $line++;
        }

        // Shipping as its own line keeps the Stripe receipt matching our order.
        if (!empty($order['shipping']) && $order['shipping'] > 0) {
            $payload["line_items[$line][price_data][currency]"]           = $currency;
            $payload["line_items[$line][price_data][unit_amount]"]        = (int) round($order['shipping'] * 100);
            $payload["line_items[$line][price_data][product_data][name]"] = 'Shipping';
            $payload["line_items[$line][quantity]"]                       = 1;
        }

        $session = $this->request('POST', '/v1/checkout/sessions', $payload);

        return [
            'redirect'  => $session['url'] ?? null,
            'reference' => $session['id'] ?? '',
            'status'    => 'pending',
        ];
    }

    public function confirm(string $sessionReference): array
    {
        $session = $this->request('GET', '/v1/checkout/sessions/' . urlencode($sessionReference));
        return [
            'paid'      => ($session['payment_status'] ?? '') === 'paid',
            'reference' => $session['payment_intent'] ?? ($session['id'] ?? null),
            'raw'       => $session,
        ];
    }

    /** Verify a webhook signature (t=,v1= scheme) before trusting the payload. */
    public function verifyWebhook(string $payload, string $signatureHeader): bool
    {
        $secret = (string) App::config('payments.stripe_webhook');
        if ($secret === '') {
            return false;
        }
        $parts = [];
        foreach (explode(',', $signatureHeader) as $chunk) {
            [$k, $v] = array_pad(explode('=', trim($chunk), 2), 2, '');
            $parts[$k][] = $v;
        }
        $timestamp = $parts['t'][0] ?? '';
        if ($timestamp === '' || abs(time() - (int) $timestamp) > 300) {
            return false; // replay window
        }
        $expected = hash_hmac('sha256', $timestamp . '.' . $payload, $secret);
        foreach ($parts['v1'] ?? [] as $candidate) {
            if (hash_equals($expected, $candidate)) {
                return true;
            }
        }
        return false;
    }

    private function request(string $method, string $path, array $params = []): array
    {
        $url = 'https://api.stripe.com' . $path;
        $ch  = curl_init();

        $options = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 20,
            CURLOPT_HTTPHEADER     => [
                'Authorization: Bearer ' . $this->secret,
                'Stripe-Version: 2024-06-20',
            ],
        ];

        if ($method === 'POST') {
            $options[CURLOPT_POST]       = true;
            $options[CURLOPT_POSTFIELDS] = http_build_query($params);
        }
        $options[CURLOPT_URL] = $url;

        curl_setopt_array($ch, $options);
        $body   = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error  = curl_error($ch);
        curl_close($ch);

        if ($body === false) {
            throw new \RuntimeException('Stripe request failed: ' . $error);
        }
        $decoded = json_decode($body, true) ?: [];
        if ($status >= 400) {
            $message = $decoded['error']['message'] ?? 'Stripe returned HTTP ' . $status;
            throw new \RuntimeException($message);
        }
        return $decoded;
    }
}
