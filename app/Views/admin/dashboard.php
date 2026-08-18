<?php
/** @var array $stats @var array $series @var array $recent @var array $lowStock @var array $topSellers */
$peak = max(1, max(array_column($series, 'amount')));
?>
<div class="admin-head">
  <div>
    <h1>Dashboard</h1>
    <p class="muted" style="margin:0">Everything that moved in the last two weeks.</p>
  </div>
  <a class="btn btn-accent" href="<?= url('/admin/products/create') ?>">+ Add product</a>
</div>

<div class="tiles">
  <div class="tile"><small>Revenue</small><strong><?= e(money($stats['revenue'])) ?></strong><span class="delta">Paid orders only</span></div>
  <div class="tile"><small>Orders</small><strong><?= (int) $stats['orders_total'] ?></strong><span class="delta"><?= (int) $stats['orders_pending'] ?> awaiting payment</span></div>
  <div class="tile"><small>Customers</small><strong><?= (int) $stats['customers'] ?></strong><span class="delta">Registered accounts</span></div>
  <div class="tile"><small>Live products</small><strong><?= (int) $stats['products'] ?></strong><span class="delta"><?= (int) $stats['low_stock'] ?> low on stock</span></div>
</div>

<div class="grid grid-2" style="align-items:start">
  <div class="panel">
    <div class="row-between" style="margin-bottom:1rem">
      <h3 style="margin:0">Revenue, last 14 days</h3>
      <span class="faint"><?= e(money(array_sum(array_column($series, 'amount')))) ?> total</span>
    </div>
    <div class="chart">
      <?php foreach ($series as $point): ?>
        <div style="height:<?= max(3, (int) round(($point['amount'] / $peak) * 100)) ?>%"
             title="<?= e($point['day'] . ' · ' . money($point['amount'])) ?>"></div>
      <?php endforeach; ?>
    </div>
    <div class="chart-labels">
      <span><?= e(date('j M', strtotime($series[0]['day']))) ?></span>
      <span><?= e(date('j M', strtotime(end($series)['day']))) ?></span>
    </div>
  </div>

  <div class="panel">
    <h3>Best sellers</h3>
    <?php if (!$topSellers): ?>
      <p class="muted">No sales recorded yet.</p>
    <?php else: ?>
      <table class="data">
        <thead><tr><th>Product</th><th>Units</th><th>Revenue</th></tr></thead>
        <tbody>
        <?php foreach ($topSellers as $t): ?>
          <tr>
            <td data-label="Product"><?= e($t['product_name']) ?></td>
            <td data-label="Units"><?= (int) $t['units'] ?></td>
            <td data-label="Revenue"><?= e(money($t['revenue'])) ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>
</div>

<div class="grid grid-2" style="align-items:start;margin-top:1.5rem">
  <div class="panel">
    <div class="row-between" style="margin-bottom:.6rem">
      <h3 style="margin:0">Latest orders</h3>
      <a class="link-arrow" href="<?= url('/admin/orders') ?>">All orders →</a>
    </div>
    <?php if (!$recent): ?>
      <p class="muted">No orders yet.</p>
    <?php else: ?>
      <table class="data">
        <thead><tr><th>Reference</th><th>Customer</th><th>Total</th><th>Status</th></tr></thead>
        <tbody>
        <?php foreach ($recent as $o): ?>
          <tr onclick="location.href='<?= url('/admin/orders/' . (int) $o['id']) ?>'" style="cursor:pointer">
            <td data-label="Reference"><strong><?= e($o['reference']) ?></strong></td>
            <td data-label="Customer"><?= e($o['customer_name']) ?></td>
            <td data-label="Total"><?= e(money($o['total'])) ?></td>
            <td data-label="Status"><span class="status status-<?= e($o['status']) ?>"><?= e($o['status']) ?></span></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>

  <div class="panel">
    <div class="row-between" style="margin-bottom:.6rem">
      <h3 style="margin:0">Running low</h3>
      <a class="link-arrow" href="<?= url('/admin/products') ?>">Inventory →</a>
    </div>
    <?php if (!$lowStock): ?>
      <p class="muted">Stock levels are healthy.</p>
    <?php else: ?>
      <table class="data">
        <thead><tr><th>Product</th><th>Stock</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($lowStock as $p): ?>
          <tr>
            <td data-label="Product"><?= e($p['name']) ?></td>
            <td data-label="Stock"><span class="status <?= (int) $p['stock'] === 0 ? 'status-cancelled' : 'status-pending' ?>"><?= (int) $p['stock'] ?> left</span></td>
            <td data-label=""><a class="link-arrow" href="<?= url('/admin/products/' . (int) $p['id'] . '/edit') ?>">Restock →</a></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>
</div>
