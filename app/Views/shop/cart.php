<?php /** @var array $summary */ $items = $summary['items']; ?>
<div class="wrap section-tight">
  <h1>Your basket</h1>
</div>

<div class="wrap" style="padding-bottom:4rem">
<?php if (!$items): ?>
  <div class="panel center" style="padding:3.5rem 1.5rem">
    <h3>Your basket is empty</h3>
    <p class="muted">Once you add something it will show up here.</p>
    <a class="btn btn-accent" href="<?= url('/shop') ?>">Start shopping</a>
  </div>
<?php else: ?>
  <div class="cart-layout">
    <form class="panel" method="post" action="<?= url('/cart/update') ?>">
      <?= csrf_field() ?>
      <table class="data">
        <thead>
          <tr><th>Product</th><th>Price</th><th>Qty</th><th>Total</th><th></th></tr>
        </thead>
        <tbody>
        <?php foreach ($items as $line): $p = $line['product']; ?>
          <tr>
            <td data-label="Product">
              <div class="row">
                <img class="thumb" src="<?= e(media($p['image'])) ?>" alt="">
                <div>
                  <a href="<?= url('/product/' . $p['slug']) ?>"><strong><?= e($p['name']) ?></strong></a>
                  <div class="faint"><?= e($p['sku']) ?></div>
                </div>
              </div>
            </td>
            <td data-label="Price"><?= e(money($line['unit'])) ?></td>
            <td data-label="Qty">
              <input class="input" style="width:74px" type="number" min="0" max="<?= (int) $p['stock'] ?>"
                     name="quantities[<?= (int) $p['id'] ?>]" value="<?= (int) $line['quantity'] ?>">
            </td>
            <td data-label="Total"><strong><?= e(money($line['subtotal'])) ?></strong></td>
            <td data-label="">
              <button class="btn btn-sm btn-danger" form="remove-<?= (int) $p['id'] ?>">Remove</button>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>

      <div class="row-between" style="margin-top:1.2rem">
        <a class="link-arrow" href="<?= url('/shop') ?>">← Continue shopping</a>
        <button class="btn btn-ghost btn-sm">Update basket</button>
      </div>
    </form>

    <?php foreach ($items as $line): ?>
      <form id="remove-<?= (int) $line['product']['id'] ?>" method="post" action="<?= url('/cart/remove') ?>" hidden>
        <?= csrf_field() ?>
        <input type="hidden" name="product_id" value="<?= (int) $line['product']['id'] ?>">
      </form>
    <?php endforeach; ?>

    <aside class="panel">
      <h3>Order summary</h3>
      <div class="summary-row"><span>Subtotal</span><span><?= e(money($summary['subtotal'])) ?></span></div>
      <div class="summary-row">
        <span>Shipping</span>
        <span><?= $summary['shipping'] > 0 ? e(money($summary['shipping'])) : 'Free' ?></span>
      </div>
      <?php if ($summary['shipping'] > 0): ?>
        <p class="faint">Spend <?= e(money(75 - $summary['subtotal'])) ?> more for free delivery.</p>
      <?php endif; ?>
      <div class="summary-row summary-total"><span>Total</span><span><?= e(money($summary['total'])) ?></span></div>
      <a class="btn btn-accent btn-block" style="margin-top:1rem" href="<?= url('/checkout') ?>">Checkout</a>
      <p class="faint center" style="margin:.8rem 0 0">Secure payment · Encrypted in transit</p>
    </aside>
  </div>
<?php endif; ?>
</div>
