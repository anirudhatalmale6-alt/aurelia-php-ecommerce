<nav class="side-nav">
  <a href="<?= url('/account') ?>" class="<?= trim(active('/account')) === 'is-active' && (parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) === '/account') ? 'is-active' : '' ?>">Orders</a>
  <a href="<?= url('/account/profile') ?>" class="<?= str_starts_with(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '', '/account/profile') ? 'is-active' : '' ?>">Profile</a>
  <a href="<?= url('/shop') ?>">Keep shopping</a>
  <form method="post" action="<?= url('/logout') ?>" style="margin-top:.5rem">
    <?= csrf_field() ?>
    <button class="btn btn-ghost btn-sm btn-block">Sign out</button>
  </form>
</nav>
