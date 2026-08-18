<?php
/** @var array $summary @var string $gateway */
use App\Core\Auth;

$user = Auth::user();
?>
<div class="wrap section-tight">
  <p class="crumbs"><a href="<?= url('/cart') ?>">Basket</a> / Checkout</p>
  <h1>Checkout</h1>
</div>

<form class="wrap cart-layout" method="post" action="<?= url('/checkout') ?>" style="padding-bottom:4rem">
  <?= csrf_field() ?>

  <div class="panel">
    <h3>Delivery details</h3>

    <?php if (!Auth::check()): ?>
      <p class="demo-note">Checking out as a guest. <a href="<?= url('/login') ?>">Sign in</a> to keep this order in your history.</p>
    <?php endif; ?>

    <label class="field">
      <span>Full name</span>
      <input class="input <?= error('customer_name') ? 'is-error' : '' ?>" name="customer_name"
             value="<?= old('customer_name', $user['name'] ?? '') ?>" required>
      <?php if ($m = error('customer_name')): ?><span class="field-error"><?= e($m) ?></span><?php endif; ?>
    </label>

    <div class="card-row">
      <label class="field">
        <span>Email</span>
        <input class="input <?= error('email') ? 'is-error' : '' ?>" type="email" name="email"
               value="<?= old('email', $user['email'] ?? '') ?>" required>
        <?php if ($m = error('email')): ?><span class="field-error"><?= e($m) ?></span><?php endif; ?>
      </label>
      <label class="field">
        <span>Phone <span class="faint">(optional)</span></span>
        <input class="input" name="phone" value="<?= old('phone', $user['phone'] ?? '') ?>">
      </label>
    </div>

    <label class="field">
      <span>Address</span>
      <input class="input <?= error('address_line1') ? 'is-error' : '' ?>" name="address_line1"
             value="<?= old('address_line1') ?>" required>
      <?php if ($m = error('address_line1')): ?><span class="field-error"><?= e($m) ?></span><?php endif; ?>
    </label>

    <label class="field">
      <span>Apartment, suite etc. <span class="faint">(optional)</span></span>
      <input class="input" name="address_line2" value="<?= old('address_line2') ?>">
    </label>

    <div class="card-row">
      <label class="field">
        <span>City</span>
        <input class="input <?= error('city') ? 'is-error' : '' ?>" name="city" value="<?= old('city') ?>" required>
        <?php if ($m = error('city')): ?><span class="field-error"><?= e($m) ?></span><?php endif; ?>
      </label>
      <label class="field">
        <span>Postcode</span>
        <input class="input <?= error('postcode') ? 'is-error' : '' ?>" name="postcode" value="<?= old('postcode') ?>" required>
        <?php if ($m = error('postcode')): ?><span class="field-error"><?= e($m) ?></span><?php endif; ?>
      </label>
    </div>

    <label class="field">
      <span>Country</span>
      <input class="input <?= error('country') ? 'is-error' : '' ?>" name="country" value="<?= old('country') ?>" required>
      <?php if ($m = error('country')): ?><span class="field-error"><?= e($m) ?></span><?php endif; ?>
    </label>

    <label class="field">
      <span>Delivery notes <span class="faint">(optional)</span></span>
      <textarea class="input" name="notes" rows="2"><?= old('notes') ?></textarea>
    </label>
  </div>

  <aside class="panel">
    <h3>Your order</h3>
    <?php foreach ($summary['items'] as $line): ?>
      <div class="summary-row">
        <span><?= e($line['product']['name']) ?> <span class="faint">× <?= (int) $line['quantity'] ?></span></span>
        <span><?= e(money($line['subtotal'])) ?></span>
      </div>
    <?php endforeach; ?>

    <div class="summary-row" style="border-top:1px solid var(--line);margin-top:.6rem;padding-top:.7rem">
      <span>Subtotal</span><span><?= e(money($summary['subtotal'])) ?></span>
    </div>
    <div class="summary-row">
      <span>Shipping</span><span><?= $summary['shipping'] > 0 ? e(money($summary['shipping'])) : 'Free' ?></span>
    </div>
    <div class="summary-row summary-total"><span>Total</span><span><?= e(money($summary['total'])) ?></span></div>

    <button class="btn btn-accent btn-block" style="margin-top:1rem">Pay <?= e(money($summary['total'])) ?></button>
    <p class="faint center" style="margin:.8rem 0 0">Processed by <?= e($gateway) ?></p>
  </aside>
</form>
