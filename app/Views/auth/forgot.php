<div class="wrap auth-shell">
  <div class="auth-card">
    <h1>Reset your password</h1>
    <p class="muted">Enter the email on your account and we'll send a link to set a new password. The link expires in one hour.</p>

    <form method="post" action="<?= url('/forgot-password') ?>">
      <?= csrf_field() ?>
      <label class="field">
        <span>Email</span>
        <input class="input <?= error('email') ? 'is-error' : '' ?>" type="email" name="email" value="<?= old('email') ?>" required autofocus>
        <?php if ($m = error('email')): ?><span class="field-error"><?= e($m) ?></span><?php endif; ?>
      </label>
      <button class="btn btn-accent btn-block">Send reset link</button>
    </form>

    <p class="center muted" style="font-size:.92rem;margin-top:1rem">
      <a class="link-arrow" href="<?= url('/login') ?>">← Back to sign in</a>
    </p>

    <div class="demo-note" style="margin-top:1rem">
      On this demo build mail is written to <code>storage/logs/mail.log</code> instead of being sent,
      so nothing leaves the server.
    </div>
  </div>
</div>
