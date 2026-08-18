<?php
/** @var ?array $product @var array $categories */
$isEdit = $product !== null;
$action = $isEdit ? url('/admin/products/' . (int) $product['id']) : url('/admin/products');

/** Prefer repopulated input after a validation failure, then the stored row. */
$val = function (string $key, $fallback = '') use ($product) {
    return old($key, $product[$key] ?? $fallback);
};
?>
<div class="admin-head">
  <div>
    <p class="crumbs"><a href="<?= url('/admin/products') ?>">Products</a> / <?= $isEdit ? 'Edit' : 'New' ?></p>
    <h1><?= $isEdit ? e($product['name']) : 'Add a product' ?></h1>
  </div>
  <?php if ($isEdit): ?>
    <div class="row">
      <a class="btn btn-ghost" href="<?= url('/product/' . $product['slug']) ?>" target="_blank">View on site ↗</a>
      <form method="post" action="<?= url('/admin/products/' . (int) $product['id'] . '/delete') ?>"
            onsubmit="return confirm('Delete this product? This cannot be undone.')">
        <?= csrf_field() ?>
        <button class="btn btn-danger">Delete</button>
      </form>
    </div>
  <?php endif; ?>
</div>

<form method="post" action="<?= e($action) ?>" enctype="multipart/form-data" class="grid grid-2" style="align-items:start">
  <?= csrf_field() ?>

  <div class="panel">
    <h3>Details</h3>

    <label class="field">
      <span>Product name</span>
      <input class="input <?= error('name') ? 'is-error' : '' ?>" name="name" value="<?= $val('name') ?>" required>
      <?php if ($m = error('name')): ?><span class="field-error"><?= e($m) ?></span><?php endif; ?>
    </label>

    <div class="card-row">
      <label class="field">
        <span>SKU <span class="faint">(auto if blank)</span></span>
        <input class="input" name="sku" value="<?= $val('sku') ?>">
      </label>
      <label class="field">
        <span>Category</span>
        <select class="input" name="category_id">
          <option value="0">— Uncategorised —</option>
          <?php foreach ($categories as $c): ?>
            <option value="<?= (int) $c['id'] ?>" <?= (int) ($product['category_id'] ?? 0) === (int) $c['id'] ? 'selected' : '' ?>>
              <?= e($c['name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </label>
    </div>

    <label class="field">
      <span>Short description <span class="faint">(shown on cards)</span></span>
      <input class="input" name="short_description" maxlength="300" value="<?= $val('short_description') ?>">
    </label>

    <label class="field">
      <span>Full description</span>
      <textarea class="input" name="description" rows="7"><?= $val('description') ?></textarea>
    </label>
  </div>

  <div class="stack" style="--gap:1.2rem">
    <div class="panel">
      <h3>Pricing &amp; stock</h3>
      <div class="card-row">
        <label class="field">
          <span>Price</span>
          <input class="input <?= error('price') ? 'is-error' : '' ?>" type="number" step="0.01" min="0"
                 name="price" value="<?= $val('price', '0.00') ?>" required>
          <?php if ($m = error('price')): ?><span class="field-error"><?= e($m) ?></span><?php endif; ?>
        </label>
        <label class="field">
          <span>Sale price <span class="faint">(0 = none)</span></span>
          <input class="input" type="number" step="0.01" min="0" name="sale_price" value="<?= $val('sale_price', '0.00') ?>">
        </label>
      </div>

      <label class="field">
        <span>Stock on hand</span>
        <input class="input" type="number" min="0" name="stock" value="<?= $val('stock', '0') ?>">
      </label>

      <label class="checkline">
        <input type="checkbox" name="is_active" value="1" <?= ($product['is_active'] ?? 1) ? 'checked' : '' ?>>
        Visible on the storefront
      </label>
      <label class="checkline">
        <input type="checkbox" name="is_featured" value="1" <?= ($product['is_featured'] ?? 0) ? 'checked' : '' ?>>
        Feature on the homepage
      </label>
    </div>

    <div class="panel">
      <h3>Image</h3>
      <?php if ($isEdit && $product['image']): ?>
        <img class="thumb" style="width:120px;height:120px;margin-bottom:.8rem" src="<?= e(media($product['image'])) ?>" alt="">
      <?php endif; ?>
      <input class="input" type="file" name="image" accept="image/jpeg,image/png,image/webp">
      <?php if ($m = error('image')): ?><span class="field-error"><?= e($m) ?></span><?php endif; ?>
      <p class="faint" style="margin:.5rem 0 0">JPEG, PNG or WebP up to 4&nbsp;MB. Resized to 1400px and re-encoded on upload.</p>
    </div>

    <button class="btn btn-accent btn-block"><?= $isEdit ? 'Save changes' : 'Publish product' ?></button>
  </div>
</form>
