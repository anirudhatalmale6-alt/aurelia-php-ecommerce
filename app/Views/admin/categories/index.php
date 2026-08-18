<?php /** @var array $categories */ ?>
<div class="admin-head">
  <div>
    <h1>Categories</h1>
    <p class="muted" style="margin:0">Categories drive the storefront navigation and filters.</p>
  </div>
</div>

<div class="grid grid-2" style="align-items:start">
  <div class="panel">
    <h3>Existing categories</h3>
    <?php if (!$categories): ?>
      <p class="muted">None yet — add one on the right.</p>
    <?php else: ?>
      <table class="data">
        <thead><tr><th>Name</th><th>Slug</th><th>Products</th><th>Order</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($categories as $c): ?>
          <tr>
            <td data-label="Name">
              <form method="post" action="<?= url('/admin/categories/' . (int) $c['id']) ?>" class="row" style="gap:.4rem">
                <?= csrf_field() ?>
                <input class="input" name="name" value="<?= e($c['name']) ?>" style="min-width:140px">
                <input class="input" name="position" type="number" value="<?= (int) $c['position'] ?>" style="width:70px">
                <input type="hidden" name="description" value="<?= e($c['description']) ?>">
                <button class="btn btn-sm btn-ghost">Save</button>
              </form>
            </td>
            <td data-label="Slug"><span class="faint"><?= e($c['slug']) ?></span></td>
            <td data-label="Products"><?= (int) $c['product_count'] ?></td>
            <td data-label="Order"><?= (int) $c['position'] ?></td>
            <td data-label="">
              <form method="post" action="<?= url('/admin/categories/' . (int) $c['id'] . '/delete') ?>"
                    onsubmit="return confirm('Delete this category? Its products stay but become uncategorised.')">
                <?= csrf_field() ?>
                <button class="btn btn-sm btn-danger">Delete</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>

  <form class="panel" method="post" action="<?= url('/admin/categories') ?>">
    <?= csrf_field() ?>
    <h3>Add a category</h3>

    <label class="field">
      <span>Name</span>
      <input class="input <?= error('name') ? 'is-error' : '' ?>" name="name" value="<?= old('name') ?>" required>
      <?php if ($m = error('name')): ?><span class="field-error"><?= e($m) ?></span><?php endif; ?>
    </label>

    <label class="field">
      <span>Description</span>
      <textarea class="input" name="description" rows="3"><?= old('description') ?></textarea>
    </label>

    <label class="field">
      <span>Menu position</span>
      <input class="input" type="number" name="position" value="<?= old('position', '0') ?>">
    </label>

    <button class="btn btn-accent btn-block">Add category</button>
  </form>
</div>
