<?php
namespace App\Controllers;

use App\Core\App;
use App\Core\Auth;
use App\Core\Cart;
use App\Core\Csrf;
use App\Core\Flash;
use App\Core\Mailer;
use App\Core\Payments\DemoGateway;
use App\Core\Payments\PaymentManager;
use App\Core\Request;
use App\Core\Validator;
use App\Core\View;
use App\Models\Category;
use App\Models\Order;

/**
 * Checkout: address capture -> order record -> gateway -> confirmation.
 *
 * The order row is written before the customer leaves for the gateway, so an
 * abandoned payment leaves a recoverable 'pending' order rather than nothing.
 */
class CheckoutController extends Controller
{
    public function show(Request $request): void
    {
        if (!Cart::items()) {
            Flash::add('error', 'Your basket is empty.');
            redirect('/shop');
        }

        $this->view('shop/checkout', [
            'title'      => 'Checkout',
            'summary'    => Cart::summary(),
            'gateway'    => PaymentManager::gateway()->name(),
            'categories' => Category::all(),
        ]);
    }

    public function place(Request $request): void
    {
        Csrf::verify();

        $items = Cart::items();
        if (!$items) {
            Flash::add('error', 'Your basket is empty.');
            redirect('/shop');
        }

        $v = new Validator($request->all());
        $v->required('customer_name')->max('customer_name', 120)
          ->required('email')->email('email')
          ->required('address_line1')->max('address_line1', 160)
          ->required('city')->required('postcode')->required('country')
          ->max('phone', 40);

        if ($v->fails()) {
            Flash::withInput($request->all(), $v->errors());
            Flash::add('error', 'Please correct the highlighted fields.');
            redirect('/checkout');
        }

        $summary   = Cart::summary();
        $reference = Order::reference();

        $orderId = Order::place([
            'reference'      => $reference,
            'user_id'        => Auth::id(),
            'customer_name'  => $request->input('customer_name'),
            'email'          => mb_strtolower($request->input('email')),
            'phone'          => $request->input('phone', ''),
            'address_line1'  => $request->input('address_line1'),
            'address_line2'  => $request->input('address_line2', ''),
            'city'           => $request->input('city'),
            'postcode'       => $request->input('postcode'),
            'country'        => $request->input('country'),
            'notes'          => $request->input('notes', ''),
            'subtotal'       => $summary['subtotal'],
            'shipping'       => $summary['shipping'],
            'tax'            => $summary['tax'],
            'total'          => $summary['total'],
            'status'         => 'pending',
            'payment_method' => PaymentManager::gateway()->name(),
            'created_at'     => date('Y-m-d H:i:s'),
            'updated_at'     => date('Y-m-d H:i:s'),
        ], $items);

        $order = Order::find($orderId);

        try {
            $checkout = PaymentManager::gateway()->createCheckout(
                $order,
                $items,
                App::url('/checkout/complete'),
                App::url('/cart')
            );
        } catch (\Throwable $e) {
            Order::setStatus($orderId, 'cancelled');
            Flash::add('error', 'Payment could not be started: ' . $e->getMessage());
            redirect('/checkout');
        }

        // Remember which order this gateway session belongs to.
        $_SESSION['_pending_order'] = [
            'order_id'  => $orderId,
            'reference' => $checkout['reference'],
        ];

        redirect($checkout['redirect'] ?? '/checkout');
    }

    /** Sandbox gateway page (demo driver only). */
    public function sandbox(Request $request): void
    {
        if (!PaymentManager::gateway() instanceof DemoGateway) {
            redirect('/checkout');
        }
        $this->view('shop/sandbox', [
            'title'   => 'Secure payment',
            'session' => $request->input('session', ''),
            'success' => $request->input('success', App::url('/checkout/complete')),
            'cancel'  => $request->input('cancel', App::url('/cart')),
            'summary' => Cart::summary(),
        ], 'layouts/blank');
    }

    /** Sandbox "Pay now" submit. */
    public function sandboxPay(Request $request): void
    {
        Csrf::verify();
        $gateway = PaymentManager::gateway();
        if (!$gateway instanceof DemoGateway) {
            redirect('/checkout');
        }
        $session = $request->input('session', '');
        $gateway->markPaid($session);
        redirect('/checkout/complete?session_id=' . urlencode($session));
    }

    /** Customer returns from the gateway. */
    public function complete(Request $request): void
    {
        $pending = $_SESSION['_pending_order'] ?? null;
        $session = $request->input('session_id', $pending['reference'] ?? '');

        if (!$pending || !$session) {
            Flash::add('error', 'We could not match that payment to an order.');
            redirect('/');
        }

        $result = PaymentManager::gateway()->confirm($session);
        $order  = Order::find((int) $pending['order_id']);

        if (!$order) {
            Flash::add('error', 'Order not found.');
            redirect('/');
        }

        if (!$result['paid']) {
            Flash::add('error', 'Payment was not completed. Your basket has been kept.');
            redirect('/cart');
        }

        if (Order::markPaid((int) $order['id'], (string) ($result['reference'] ?? $session))) {
            Mailer::send(
                $order['email'],
                'Order confirmation ' . $order['reference'],
                'order_confirmation',
                ['order' => Order::find((int) $order['id']), 'items' => Order::items((int) $order['id'])]
            );
        }

        Cart::clear();
        unset($_SESSION['_pending_order']);

        $order = Order::find((int) $order['id']);
        $this->view('shop/confirmation', [
            'title'      => 'Order confirmed',
            'order'      => $order,
            'items'      => Order::items((int) $order['id']),
            'categories' => Category::all(),
        ]);
    }

    /**
     * Stripe webhook - the authoritative payment confirmation.
     * Configure at: https://dashboard.stripe.com/webhooks -> checkout.session.completed
     */
    public function webhook(Request $request): void
    {
        $payload   = file_get_contents('php://input') ?: '';
        $signature = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';
        $gateway   = PaymentManager::gateway();

        if (!$gateway instanceof \App\Core\Payments\StripeGateway
            || !$gateway->verifyWebhook($payload, $signature)) {
            $this->json(['error' => 'invalid signature'], 400);
            return;
        }

        $event = json_decode($payload, true) ?: [];
        if (($event['type'] ?? '') === 'checkout.session.completed') {
            $session   = $event['data']['object'] ?? [];
            $reference = $session['client_reference_id'] ?? '';
            $order     = $reference ? Order::findByReference($reference) : null;

            if ($order) {
                Order::markPaid((int) $order['id'], (string) ($session['payment_intent'] ?? $session['id']));
                Mailer::send(
                    $order['email'],
                    'Order confirmation ' . $order['reference'],
                    'order_confirmation',
                    ['order' => Order::find((int) $order['id']), 'items' => Order::items((int) $order['id'])]
                );
            }
        }

        $this->json(['received' => true]);
    }
}
