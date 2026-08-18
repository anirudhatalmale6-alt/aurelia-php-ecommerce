<?php
/** @var array $order @var array $items */
use App\Core\View;

ob_start(); ?>
<h2 style="margin:0 0 12px;font-size:20px">Thanks for your order, <?= e(explode(' ', $order['customer_name'])[0]) ?>.</h2>
<p style="margin:0 0 16px">We've received payment and your parcel is being packed. Your reference is
   <strong><?= e($order['reference']) ?></strong>.</p>

<table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;margin:16px 0">
  <?php foreach ($items as $i): ?>
    <tr>
      <td style="padding:8px 0;border-bottom:1px solid #efeae2">
        <?= e($i['product_name']) ?> <span style="color:#8b8378">× <?= (int) $i['quantity'] ?></span>
      </td>
      <td align="right" style="padding:8px 0;border-bottom:1px solid #efeae2"><?= e(money($i['line_total'])) ?></td>
    </tr>
  <?php endforeach; ?>
  <tr>
    <td style="padding:8px 0">Shipping</td>
    <td align="right" style="padding:8px 0"><?= $order['shipping'] > 0 ? e(money($order['shipping'])) : 'Free' ?></td>
  </tr>
  <tr>
    <td style="padding:8px 0;font-weight:bold;border-top:1px solid #e6e0d8">Total paid</td>
    <td align="right" style="padding:8px 0;font-weight:bold;border-top:1px solid #e6e0d8"><?= e(money($order['total'])) ?></td>
  </tr>
</table>

<p style="margin:0 0 6px;font-weight:bold">Delivering to</p>
<p style="margin:0 0 20px;color:#56514a">
  <?= e($order['customer_name']) ?><br>
  <?= e($order['address_line1']) ?><br>
  <?= e($order['city']) ?>, <?= e($order['postcode']) ?><br>
  <?= e($order['country']) ?>
</p>

<a href="<?= url('/account') ?>"
   style="display:inline-block;background:#a84f2e;color:#ffffff;text-decoration:none;padding:11px 20px;border-radius:999px">
  Track this order
</a>
<?php $body = ob_get_clean();
echo View::partial('emails/_layout', ['body' => $body]);
