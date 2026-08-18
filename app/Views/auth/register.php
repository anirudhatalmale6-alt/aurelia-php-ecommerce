<div class="wrap auth-shell">
  <div class="auth-card">
    <h1>Create an account</h1>
    <p class="muted">Order history, faster checkout, and nothing else — we don't send spam.</p>

    <form method="post" action="<?= url('/register') ?>">
      <?= csrf_field() ?>

      <label class="field">
        <span>Full name</span>
        <input class="input <?= error('name') ? 'is-error' : '' ?>" name="name" value="<?= old('name') ?>" required autofocus>
        <?php if ($m = error('name')): ?><span class="field-error"><?= e($m) ?></span><?php endif; ?>
      </label>

      <label class="field">
        <span>Email</span>
        <input class="input <?= error('email') ? 'is-error' : '' ?>" type="email" name="email" value="<?= old('email') ?>" required>
        <?php if ($m = error('email')): ?><span class="field-error"><?= e($m) ?></span><?php endif; ?>
      </label>

      <label class="field">
        <span>Password</span>
        <input class="input <?= error('password') ? 'is-error' : '' ?>" type="password" name="password" required>
        <?php if ($m = error('password')): ?><span class="field-error"><?= e($m) ?></span>
        <?php else: ?><span class="faint">At least 8 characters, with a letter and a number.</span><?php endif; ?>
      </label>

      <label class="field">
        <span>Confirm password</span>
        <input class="input <?= error('password_confirmation') ? 'is-error' : '' ?>" type="password" name="password_confirmation" required>
        <?php if ($m = error('password_confirmation')): ?><span class="field-error"><?= e($m) ?></span><?php endif; ?>
      </label>

      <button class="btn btn-accent btn-block">Create account</button>
    </form>

    <p class="center muted" style="font-size:.92rem;margin-top:1rem">
      Already registered? <a class="link-arrow" href="<?= url('/login') ?>">Sign in</a>
    </p>
  </div>
</div>
