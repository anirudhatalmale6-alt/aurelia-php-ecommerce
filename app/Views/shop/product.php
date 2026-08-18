<?php
/** @var array $product @var array $related */
use App\Models\Product;

$price  = Product::price($product);
$onSale = (float) $product['sale_price'] > 0 && (float) $product['sale_price'] < (float) $product['price'];
$stock  = (int) $product['stock'];
?>
<div class="wrap section-tight">
  <p class="crumbs">
    <a href="<?= url('/') ?>">Home</a> /
    <?php if ($product['category_slug']): ?>
      <a href="<?= url('/shop?category=' . urlencode($product['category_slug'])) ?>"><?= e($product['category_name']) ?></a> /
    <?php endif; ?>
    <?= e($product['name']) ?>
  </p>

  <div class="product-view">
    <div class="product-gallery">
      <img src="<?= e(media($product['image'])) ?>" alt="<?= e($product['name']) ?>">
    </div>

    <div>
      <span class="card-cat"><?= e($product['category_name'] ?? 'Uncategorised') ?></span>
      <h1 style="margin:.4rem 0 .6rem"><?= e($product['name']) ?></h1>

      <p class="price-lg">
        <?php if ($onSale): ?><span class="price-was"><?= e(money($product['price'])) ?></span><?php endif; ?>
        <?= e(money($price)) ?>
      </p>

      <p class="muted"><?= nl2br(e($product['description'])) ?></p>

      <p class="stock-pill">
        <?php if ($stock < 1): ?>
          <span class="dot dot-out"></span> Out of stock — back soon
        <?php elseif ($stock <= 5): ?>
          <span class="dot dot-low"></span> Only <?= $stock ?> left
        <?php else: ?>
          <span class="dot"></span> In stock, ships within 48 hours
        <?php endif; ?>
      </p>

      <?php if ($stock > 0): ?>
        <form method="post" action="<?= url('/cart/add') ?>" class="row" style="margin:1.4rem 0">
          <?= csrf_field() ?>
          <input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>">
          <input type="hidden" name="redirect" value="cart">
          <div class="qty">
            <button type="button" data-qty="-1" aria-label="Decrease quantity">−</button>
            <input type="number" name="quantity" id="qty-input" value="1" min="1" max="<?= $stock ?>">
            <button type="button" data-qty="1" aria-label="Increase quantity">+</button>
          </div>
          <button class="btn btn-accent">Add to basket</button>
        </form>
      <?php else: ?>
        <p><button class="btn" disabled>Add to basket</button></p>
      <?php endif; ?>

      <div style="margin-top:1.5rem">
        <div class="spec"><span class="muted">SKU</span><span><?= e($product['sku']) ?></span></div>
        <div class="spec"><span class="muted">Category</span><span><?= e($product['category_name'] ?? '—') ?></span></div>
        <div class="spec"><span class="muted">Delivery</span><span>Free over <?= e(money(75)) ?></span></div>
        <div class="spec"><span class="muted">Returns</span><span>30 days, unused</span></div>
      </div>
    </div>
  </div>
</div>

<?php if ($related): ?>
<section class="wrap section">
  <div class="section-head"><div><h2>You may also like</h2></div></div>
  <div class="grid grid-4">
    <?php foreach ($related as $p): ?><?= product_card($p) ?><?php endforeach; ?>
  </div>
</section>
<?php endif; ?>
