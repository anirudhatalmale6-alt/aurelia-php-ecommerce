<?php
/** @var array $orders @var string $status @var string $search */
use App\Models\Order;
?>
<div class="admin-head">
  <div>
    <h1>Orders</h1>
    <p class="muted" style="margin:0"><?= count($orders) ?> order<?= count($orders) === 1 ? '' : 's' ?> shown.</p>
  </div>
  <form method="get" action="<?= url('/admin/orders') ?>" class="row">
    <select class="input" name="status" onchange="this.form.submit()" style="width:auto">
      <option value="">All statuses</option>
      <?php foreach (Order::STATUSES as $s): ?>
        <option value="<?= e($s) ?>" <?= $status === $s ? 'selected' : '' ?>><?= e(ucfirst($s)) ?></option>
      <?php endforeach; ?>
    </select>
    <input class="input" type="search" name="q" placeholder="Reference, name or email" value="<?= e($search) ?>">
    <button class="btn btn-ghost btn-sm">Search</button>
  </form>
</div>

<div class="panel">
<?php if (!$orders): ?>
  <p class="muted">No orders match that filter.</p>
<?php else: ?>
  <table class="data">
    <thead><tr><th>Reference</th><th>Customer</th><th>Placed</th><th>Items</th><th>Total</th><th>Status</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($orders as $o): ?>
      <tr>
        <td data-label="Reference"><strong><?= e($o['reference']) ?></strong></td>
        <td data-label="Customer"><?= e($o['customer_name']) ?><div class="faint"><?= e($o['email']) ?></div></td>
        <td data-label="Placed"><?= e(date('j M Y', strtotime($o['created_at']))) ?></td>
        <td data-label="Items"><?= (int) $o['item_count'] ?></td>
        <td data-label="Total"><?= e(money($o['total'])) ?></td>
        <td data-label="Status"><span class="status status-<?= e($o['status']) ?>"><?= e($o['status']) ?></span></td>
        <td data-label=""><a class="btn btn-sm btn-ghost" href="<?= url('/admin/orders/' . (int) $o['id']) ?>">Open</a></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
<?php endif; ?>
</div>
