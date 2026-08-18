<?php
use App\Core\Auth;
use App\Core\View;

$user = Auth::user();
?>
<div class="wrap section-tight">
  <h1>Profile settings</h1>
</div>

<div class="wrap account-layout" style="padding-bottom:4rem">
  <?= View::partial('account/_nav') ?>

  <div class="stack" style="--gap:1.2rem">
    <form class="panel" method="post" action="<?= url('/account/profile') ?>">
      <?= csrf_field() ?>
      <h3>Your details</h3>

      <label class="field">
        <span>Full name</span>
        <input class="input <?= error('name') ? 'is-error' : '' ?>" name="name" value="<?= old('name', $user['name']) ?>" required>
        <?php if ($m = error('name')): ?><span class="field-error"><?= e($m) ?></span><?php endif; ?>
      </label>

      <div class="card-row">
        <label class="field">
          <span>Email</span>
          <input class="input <?= error('email') ? 'is-error' : '' ?>" type="email" name="email" value="<?= old('email', $user['email']) ?>" required>
          <?php if ($m = error('email')): ?><span class="field-error"><?= e($m) ?></span><?php endif; ?>
        </label>
        <label class="field">
          <span>Phone</span>
          <input class="input" name="phone" value="<?= old('phone', $user['phone'] ?? '') ?>">
        </label>
      </div>

      <button class="btn btn-accent">Save changes</button>
    </form>

    <form class="panel" method="post" action="<?= url('/account/password') ?>">
      <?= csrf_field() ?>
      <h3>Change password</h3>

      <label class="field">
        <span>Current password</span>
        <input class="input <?= error('current_password') ? 'is-error' : '' ?>" type="password" name="current_password" required>
        <?php if ($m = error('current_password')): ?><span class="field-error"><?= e($m) ?></span><?php endif; ?>
      </label>

      <div class="card-row">
        <label class="field">
          <span>New password</span>
          <input class="input <?= error('password') ? 'is-error' : '' ?>" type="password" name="password" required>
          <?php if ($m = error('password')): ?><span class="field-error"><?= e($m) ?></span><?php endif; ?>
        </label>
        <label class="field">
          <span>Confirm new password</span>
          <input class="input <?= error('password_confirmation') ? 'is-error' : '' ?>" type="password" name="password_confirmation" required>
          <?php if ($m = error('password_confirmation')): ?><span class="field-error"><?= e($m) ?></span><?php endif; ?>
        </label>
      </div>

      <button class="btn btn-ghost">Update password</button>
    </form>
  </div>
</div>
