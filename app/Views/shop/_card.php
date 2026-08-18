<?php
/** Product card. @var array $p */
use App\Models\Product;

$price   = Product::price($p);
$onSale  = (float) $p['sale_price'] > 0 && (float) $p['sale_price'] < (float) $p['price'];
$stock   = (int) $p['stock'];
?>
<article class="card">
  <a class="card-media" href="<?= url('/product/' . $p['slug']) ?>">
    <img src="<?= e(media($p['image'])) ?>" alt="<?= e($p['name']) ?>" loading="lazy">
    <?php if ($stock < 1): ?>
      <span class="badge badge-out">Sold out</span>
    <?php elseif ($onSale): ?>
      <span class="badge badge-sale">Sale</span>
    <?php elseif ($stock <= 5): ?>
      <span class="badge badge-low">Low stock</span>
    <?php endif; ?>
  </a>
  <div class="card-body">
    <?php if (!empty($p['category_name'])): ?>
      <span class="card-cat"><?= e($p['category_name']) ?></span>
    <?php endif; ?>
    <a href="<?= url('/product/' . $p['slug']) ?>"><h3 class="card-title"><?= e($p['name']) ?></h3></a>
    <p class="faint" style="margin:0 0 .8rem"><?= e(excerpt($p['short_description'], 62)) ?></p>
    <div class="card-foot">
      <span class="price">
        <?php if ($onSale): ?><span class="price-was"><?= e(money($p['price'])) ?></span><?php endif; ?>
        <?= e(money($price)) ?>
      </span>
      <?php if ($stock > 0): ?>
        <form method="post" action="<?= url('/cart/add') ?>">
          <?= csrf_field() ?>
          <input type="hidden" name="product_id" value="<?= (int) $p['id'] ?>">
          <button class="btn btn-sm btn-ghost">Add</button>
        </form>
      <?php else: ?>
        <span class="faint">Out of stock</span>
      <?php endif; ?>
    </div>
  </div>
</article>
