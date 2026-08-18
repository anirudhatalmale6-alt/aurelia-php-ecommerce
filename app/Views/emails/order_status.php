<?php
/** @var array $order @var string $status */
use App\Core\View;

$copy = [
    'shipped'   => 'Your parcel has left the studio and is on its way.',
    'completed' => 'Your order is complete. We hope you love it.',
    'cancelled' => 'Your order has been cancelled. Any payment taken will be returned.',
    'refunded'  => 'Your refund has been issued and should appear within 5 working days.',
][$status] ?? 'There is an update on your order.';

ob_start(); ?>
<h2 style="margin:0 0 12px;font-size:20px">Order <?= e($order['reference']) ?> is now <?= e($status) ?></h2>
<p style="margin:0 0 16px"><?= e($copy) ?></p>
<p style="margin:0 0 20px;color:#56514a">Order total: <strong><?= e(money($order['total'])) ?></strong></p>
<a href="<?= url('/account') ?>"
   style="display:inline-block;background:#a84f2e;color:#ffffff;text-decoration:none;padding:11px 20px;border-radius:999px">
  View order
</a>
<?php $body = ob_get_clean();
echo View::partial('emails/_layout', ['body' => $body]);
