<?php /** @var string $email @var string $token */ ?>
<div class="wrap auth-shell">
  <div class="auth-card">
    <h1>Choose a new password</h1>
    <p class="muted">Resetting the password for <strong><?= e($email) ?></strong>.</p>

    <form method="post" action="<?= url('/reset-password') ?>">
      <?= csrf_field() ?>
      <input type="hidden" name="email" value="<?= e($email) ?>">
      <input type="hidden" name="token" value="<?= e($token) ?>">

      <label class="field">
        <span>New password</span>
        <input class="input <?= error('password') ? 'is-error' : '' ?>" type="password" name="password" required autofocus>
        <?php if ($m = error('password')): ?><span class="field-error"><?= e($m) ?></span>
        <?php else: ?><span class="faint">At least 8 characters, with a letter and a number.</span><?php endif; ?>
      </label>

      <label class="field">
        <span>Confirm new password</span>
        <input class="input <?= error('password_confirmation') ? 'is-error' : '' ?>" type="password" name="password_confirmation" required>
        <?php if ($m = error('password_confirmation')): ?><span class="field-error"><?= e($m) ?></span><?php endif; ?>
      </label>

      <button class="btn btn-accent btn-block">Update password</button>
    </form>
  </div>
</div>
