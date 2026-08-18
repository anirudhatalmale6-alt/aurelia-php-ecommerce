<?php
namespace App\Core\Payments;

use App\Core\App;

/** Resolves the configured gateway. */
class PaymentManager
{
    private static ?Gateway $gateway = null;

    public static function gateway(): Gateway
    {
        if (self::$gateway !== null) {
            return self::$gateway;
        }

        $driver = App::config('payments.driver', 'demo');

        // Fall back to the sandbox rather than breaking checkout if Stripe is
        // selected but the keys have not been installed yet.
        if ($driver === 'stripe' && App::config('payments.stripe_secret')) {
            return self::$gateway = new StripeGateway();
        }
        return self::$gateway = new DemoGateway();
    }
}
