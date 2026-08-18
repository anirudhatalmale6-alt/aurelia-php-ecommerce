<?php /** @var array $customers */ ?>
<div class="admin-head">
  <div>
    <h1>Customers</h1>
    <p class="muted" style="margin:0"><?= count($customers) ?> registered account<?= count($customers) === 1 ? '' : 's' ?>.</p>
  </div>
</div>

<div class="panel">
  <table class="data">
    <thead><tr><th>Name</th><th>Email</th><th>Phone</th><th>Role</th><th>Orders</th><th>Joined</th></tr></thead>
    <tbody>
    <?php foreach ($customers as $c): ?>
      <tr>
        <td data-label="Name"><strong><?= e($c['name']) ?></strong></td>
        <td data-label="Email"><?= e($c['email']) ?></td>
        <td data-label="Phone"><?= e($c['phone'] ?: '—') ?></td>
        <td data-label="Role"><span class="status <?= $c['role'] === 'admin' ? 'status-processing' : '' ?>"><?= e($c['role']) ?></span></td>
        <td data-label="Orders"><?= (int) $c['order_count'] ?></td>
        <td data-label="Joined"><?= e($c['created_at'] ? date('j M Y', strtotime($c['created_at'])) : '—') ?></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
