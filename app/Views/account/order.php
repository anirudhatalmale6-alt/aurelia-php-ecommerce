<?php
/** @var array $order @var array $items */
use App\Core\View;
?>
<div class="wrap section-tight">
  <p class="crumbs"><a href="<?= url('/account') ?>">My account</a> / <?= e($order['reference']) ?></p>
  <h1>Order <?= e($order['reference']) ?></h1>
</div>

<div class="wrap account-layout" style="padding-bottom:4rem">
  <?= View::partial('account/_nav') ?>

  <div class="stack" style="--gap:1.2rem">
    <div class="panel">
      <div class="row-between">
        <div><span class="faint">Placed</span><br><?= e(date('j M Y, H:i', strtotime($order['created_at']))) ?></div>
        <div><span class="faint">Payment</span><br><?= e($order['payment_method']) ?></div>
        <div><span class="faint">Total</span><br><strong><?= e(money($order['total'])) ?></strong></div>
        <span class="status status-<?= e($order['status']) ?>"><?= e($order['status']) ?></span>
      </div>
    </div>

    <div class="panel">
      <h3>Items</h3>
      <table class="data">
        <thead><tr><th>Product</th><th>Unit</th><th>Qty</th><th>Total</th></tr></thead>
        <tbody>
        <?php foreach ($items as $i): ?>
          <tr>
            <td data-label="Product"><?= e($i['product_name']) ?><div class="faint"><?= e($i['sku']) ?></div></td>
            <td data-label="Unit"><?= e(money($i['unit_price'])) ?></td>
            <td data-label="Qty"><?= (int) $i['quantity'] ?></td>
            <td data-label="Total"><?= e(money($i['line_total'])) ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>

      <div class="summary-row"><span>Subtotal</span><span><?= e(money($order['subtotal'])) ?></span></div>
      <div class="summary-row"><span>Shipping</span><span><?= $order['shipping'] > 0 ? e(money($order['shipping'])) : 'Free' ?></span></div>
      <div class="summary-row summary-total"><span>Total</span><span><?= e(money($order['total'])) ?></span></div>
    </div>

    <div class="panel">
      <h3>Delivery address</h3>
      <p class="muted" style="margin:0">
        <?= e($order['customer_name']) ?><br>
        <?= e($order['address_line1']) ?><?= $order['address_line2'] ? '<br>' . e($order['address_line2']) : '' ?><br>
        <?= e($order['city']) ?>, <?= e($order['postcode']) ?><br>
        <?= e($order['country']) ?>
      </p>
    </div>
  </div>
</div>
