<div class="wrap auth-shell">
  <div class="auth-card">
    <h1>Welcome back</h1>
    <p class="muted">Sign in to see your orders and check out faster.</p>

    <form method="post" action="<?= url('/login') ?>">
      <?= csrf_field() ?>
      <label class="field">
        <span>Email</span>
        <input class="input <?= error('email') ? 'is-error' : '' ?>" type="email" name="email"
               value="<?= old('email') ?>" required autofocus>
        <?php if ($m = error('email')): ?><span class="field-error"><?= e($m) ?></span><?php endif; ?>
      </label>

      <label class="field">
        <span>Password</span>
        <input class="input" type="password" name="password" required>
      </label>

      <button class="btn btn-accent btn-block">Sign in</button>
    </form>

    <div class="divider">or</div>
    <p class="center muted" style="font-size:.92rem">
      New here? <a class="link-arrow" href="<?= url('/register') ?>">Create an account</a><br>
      <a href="<?= url('/forgot-password') ?>" class="faint">Forgotten your password?</a>
    </p>

    <div class="demo-note" style="margin-top:1rem">
      <strong>Demo logins</strong><br>
      Admin: admin@aurelia.test / AdminPass123<br>
      Customer: customer@aurelia.test / CustomerPass123
    </div>
  </div>
</div>
