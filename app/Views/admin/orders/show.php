<?php
/** @var array $order @var array $items */
use App\Models\Order;
?>
<div class="admin-head">
  <div>
    <p class="crumbs"><a href="<?= url('/admin/orders') ?>">Orders</a> / <?= e($order['reference']) ?></p>
    <h1>Order <?= e($order['reference']) ?></h1>
  </div>
  <form method="post" action="<?= url('/admin/orders/' . (int) $order['id'] . '/status') ?>" class="row">
    <?= csrf_field() ?>
    <select class="input" name="status" style="width:auto">
      <?php foreach (Order::STATUSES as $s): ?>
        <option value="<?= e($s) ?>" <?= $order['status'] === $s ? 'selected' : '' ?>><?= e(ucfirst($s)) ?></option>
      <?php endforeach; ?>
    </select>
    <button class="btn btn-accent">Update status</button>
  </form>
</div>

<div class="grid grid-2" style="align-items:start">
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
    <div class="summary-row"><span>Shipping</span><span><?= e(money($order['shipping'])) ?></span></div>
    <div class="summary-row summary-total"><span>Total</span><span><?= e(money($order['total'])) ?></span></div>
  </div>

  <div class="stack" style="--gap:1.2rem">
    <div class="panel">
      <h3>Customer</h3>
      <p class="muted" style="margin:0">
        <?= e($order['customer_name']) ?><br>
        <?= e($order['email']) ?><br>
        <?= e($order['phone'] ?: '—') ?>
      </p>
      <h4 style="margin-top:1rem">Shipping address</h4>
      <p class="muted" style="margin:0">
        <?= e($order['address_line1']) ?><?= $order['address_line2'] ? '<br>' . e($order['address_line2']) : '' ?><br>
        <?= e($order['city']) ?>, <?= e($order['postcode']) ?><br>
        <?= e($order['country']) ?>
      </p>
      <?php if ($order['notes']): ?>
        <h4 style="margin-top:1rem">Delivery notes</h4>
        <p class="muted" style="margin:0"><?= nl2br(e($order['notes'])) ?></p>
      <?php endif; ?>
    </div>

    <div class="panel">
      <h3>Payment</h3>
      <div class="summary-row"><span>Status</span><span class="status status-<?= e($order['status']) ?>"><?= e($order['status']) ?></span></div>
      <div class="summary-row"><span>Method</span><span><?= e($order['payment_method'] ?: '—') ?></span></div>
      <div class="summary-row"><span>Reference</span><span class="faint"><?= e($order['payment_reference'] ?: '—') ?></span></div>
      <div class="summary-row"><span>Paid at</span><span><?= e($order['paid_at'] ? date('j M Y, H:i', strtotime($order['paid_at'])) : '—') ?></span></div>
    </div>
  </div>
</div>
