<?php /** @var string $session @var array $summary */ ?>
<div class="sandbox-shell">
  <div class="sandbox-card">
    <div class="lock">
      <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <rect x="4" y="10" width="16" height="10" rx="2"/><path d="M8 10V7a4 4 0 018 0v3"/>
      </svg>
      Sandbox gateway · no real card is charged
    </div>

    <h2 style="text-align:center;margin-bottom:.2rem">Aurelia</h2>
    <p class="center muted" style="font-size:.9rem">Amount due <strong><?= e(money($summary['total'])) ?></strong></p>

    <form method="post" action="<?= url('/checkout/sandbox') ?>" style="margin-top:1.4rem">
      <?= csrf_field() ?>
      <input type="hidden" name="session" value="<?= e($session) ?>">

      <label class="field">
        <span>Card number</span>
        <input class="input" value="4242 4242 4242 4242" readonly>
      </label>
      <div class="card-row">
        <label class="field"><span>Expiry</span><input class="input" value="12 / 34" readonly></label>
        <label class="field"><span>CVC</span><input class="input" value="123" readonly></label>
      </div>

      <button class="btn btn-accent btn-block">Pay <?= e(money($summary['total'])) ?></button>
      <a class="btn btn-ghost btn-block" style="margin-top:.6rem" href="<?= url('/cart') ?>">Cancel and return</a>
    </form>

    <p class="faint center" style="margin-top:1.2rem">
      This page stands in for Stripe Checkout until live keys are added.
      Switching <code>PAYMENT_DRIVER=stripe</code> sends customers to Stripe instead — nothing else changes.
    </p>
  </div>
</div>
