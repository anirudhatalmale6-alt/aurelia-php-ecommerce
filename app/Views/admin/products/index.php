<?php /** @var array $products @var string $search */ ?>
<div class="admin-head">
  <div>
    <h1>Products</h1>
    <p class="muted" style="margin:0"><?= count($products) ?> item<?= count($products) === 1 ? '' : 's' ?> in the catalogue.</p>
  </div>
  <div class="row">
    <form method="get" action="<?= url('/admin/products') ?>">
      <input class="input" type="search" name="q" placeholder="Search name or SKU" value="<?= e($search) ?>">
    </form>
    <a class="btn btn-accent" href="<?= url('/admin/products/create') ?>">+ Add product</a>
  </div>
</div>

<div class="panel">
<?php if (!$products): ?>
  <p class="muted">No products found.</p>
<?php else: ?>
  <table class="data">
    <thead>
      <tr><th></th><th>Product</th><th>Category</th><th>Price</th><th>Stock</th><th>Status</th><th></th></tr>
    </thead>
    <tbody>
    <?php foreach ($products as $p): ?>
      <tr>
        <td data-label=""><img class="thumb" src="<?= e(media($p['image'])) ?>" alt=""></td>
        <td data-label="Product">
          <strong><?= e($p['name']) ?></strong>
          <div class="faint"><?= e($p['sku']) ?></div>
        </td>
        <td data-label="Category"><?= e($p['category_name'] ?? '—') ?></td>
        <td data-label="Price">
          <?php if ((float) $p['sale_price'] > 0): ?>
            <span class="price-was"><?= e(money($p['price'])) ?></span><?= e(money($p['sale_price'])) ?>
          <?php else: ?>
            <?= e(money($p['price'])) ?>
          <?php endif; ?>
        </td>
        <td data-label="Stock">
          <span class="status <?= (int) $p['stock'] === 0 ? 'status-cancelled' : ((int) $p['stock'] <= 5 ? 'status-pending' : 'status-paid') ?>">
            <?= (int) $p['stock'] ?>
          </span>
        </td>
        <td data-label="Status">
          <span class="status <?= $p['is_active'] ? 'status-paid' : '' ?>"><?= $p['is_active'] ? 'Live' : 'Hidden' ?></span>
          <?php if ($p['is_featured']): ?><span class="status">Featured</span><?php endif; ?>
        </td>
        <td data-label="">
          <div class="row" style="gap:.4rem">
            <a class="btn btn-sm btn-ghost" href="<?= url('/admin/products/' . (int) $p['id'] . '/edit') ?>">Edit</a>
            <a class="btn btn-sm btn-ghost" href="<?= url('/product/' . $p['slug']) ?>" target="_blank">View</a>
          </div>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
<?php endif; ?>
</div>
