<?php
/** @var array $orders */
use App\Core\Auth;
use App\Core\View;

$user = Auth::user();
?>
<div class="wrap section-tight">
  <h1>Hello, <?= e(explode(' ', $user['name'])[0]) ?></h1>
  <p class="muted">Your orders and account settings live here.</p>
</div>

<div class="wrap account-layout" style="padding-bottom:4rem">
  <?= View::partial('account/_nav') ?>

  <div>
    <div class="panel">
      <h3>Order history</h3>
      <?php if (!$orders): ?>
        <p class="muted">You haven't placed an order yet.</p>
        <a class="btn btn-accent btn-sm" href="<?= url('/shop') ?>">Browse the shop</a>
      <?php else: ?>
        <table class="data">
          <thead><tr><th>Order</th><th>Date</th><th>Items</th><th>Total</th><th>Status</th><th></th></tr></thead>
          <tbody>
          <?php foreach ($orders as $o): ?>
            <tr>
              <td data-label="Order"><strong><?= e($o['reference']) ?></strong></td>
              <td data-label="Date"><?= e(date('j M Y', strtotime($o['created_at']))) ?></td>
              <td data-label="Items"><?= (int) $o['item_count'] ?></td>
              <td data-label="Total"><?= e(money($o['total'])) ?></td>
              <td data-label="Status"><span class="status status-<?= e($o['status']) ?>"><?= e($o['status']) ?></span></td>
              <td data-label=""><a class="link-arrow" href="<?= url('/account/orders/' . urlencode($o['reference'])) ?>">View →</a></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </div>
  </div>
</div>
