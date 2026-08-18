<?php /** @var array $order @var array $items */ ?>
<div class="wrap section">
  <div class="panel" style="max-width:720px;margin-inline:auto">
    <div class="center" style="margin-bottom:1.5rem">
      <div style="width:56px;height:56px;border-radius:50%;background:var(--accent-wash);display:grid;place-items:center;margin:0 auto 1rem">
        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="var(--accent)" stroke-width="2.2">
          <path d="M5 13l4 4 10-10"/>
        </svg>
      </div>
      <h1 style="margin-bottom:.3rem">Thank you — your order is confirmed</h1>
      <p class="muted">A confirmation email is on its way to <?= e($order['email']) ?>.</p>
    </div>

    <div class="row-between" style="border-top:1px solid var(--line);border-bottom:1px solid var(--line);padding:.9rem 0">
      <div><span class="faint">Order</span><br><strong><?= e($order['reference']) ?></strong></div>
      <div><span class="faint">Placed</span><br><?= e(date('j M Y', strtotime($order['created_at']))) ?></div>
      <div><span class="faint">Payment</span><br><?= e($order['payment_method']) ?></div>
      <div><span class="status status-<?= e($order['status']) ?>"><?= e($order['status']) ?></span></div>
    </div>

    <table class="data" style="margin-top:1rem">
      <tbody>
      <?php foreach ($items as $i): ?>
        <tr>
          <td data-label="Item"><?= e($i['product_name']) ?> <span class="faint">× <?= (int) $i['quantity'] ?></span></td>
          <td data-label="Total" style="text-align:right"><?= e(money($i['line_total'])) ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>

    <div class="summary-row"><span>Subtotal</span><span><?= e(money($order['subtotal'])) ?></span></div>
    <div class="summary-row"><span>Shipping</span><span><?= $order['shipping'] > 0 ? e(money($order['shipping'])) : 'Free' ?></span></div>
    <div class="summary-row summary-total"><span>Total paid</span><span><?= e(money($order['total'])) ?></span></div>

    <div style="margin-top:1.4rem">
      <h4>Delivering to</h4>
      <p class="muted" style="margin:0">
        <?= e($order['customer_name']) ?><br>
        <?= e($order['address_line1']) ?><?= $order['address_line2'] ? '<br>' . e($order['address_line2']) : '' ?><br>
        <?= e($order['city']) ?>, <?= e($order['postcode']) ?><br>
        <?= e($order['country']) ?>
      </p>
    </div>

    <div class="row" style="margin-top:1.6rem">
      <a class="btn btn-accent" href="<?= url('/shop') ?>">Continue shopping</a>
      <a class="btn btn-ghost" href="<?= url('/account') ?>">View my orders</a>
    </div>
  </div>
</div>
