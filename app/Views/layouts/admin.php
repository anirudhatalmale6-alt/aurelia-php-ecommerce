<?php
/** Admin layout. @var string $content */
use App\Core\App;
use App\Core\Auth;
use App\Core\Flash;

$nav = [
    ['/admin',            'Dashboard'],
    ['/admin/products',   'Products'],
    ['/admin/categories', 'Categories'],
    ['/admin/orders',     'Orders'],
    ['/admin/customers',  'Customers'],
];
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e(($title ?? 'Admin') . ' · ' . App::config('app.name') . ' admin') ?></title>
<link rel="icon" href="<?= url('/assets/img/favicon.svg') ?>">
<link rel="stylesheet" href="<?= url('/assets/css/app.css') ?>">
</head>
<body class="admin-body">

<div class="flash-area">
<?php foreach (Flash::messages() as $flash): ?>
  <div class="flash <?= $flash['type'] === 'error' ? 'flash-error' : '' ?>"><?= e($flash['message']) ?></div>
<?php endforeach; ?>
</div>

<div class="admin-shell">
  <aside class="admin-side">
    <a class="brand" href="<?= url('/admin') ?>">Aurel<span>ia</span></a>
    <span class="brand-sub">Control panel</span>

    <nav class="admin-nav">
      <?php foreach ($nav as [$path, $label]):
        $current = $path === '/admin'
            ? (($_SERVER['REQUEST_URI'] ?? '') === '/admin')
            : str_starts_with(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '', $path); ?>
        <a href="<?= url($path) ?>" class="<?= $current ? 'is-active' : '' ?>"><?= e($label) ?></a>
      <?php endforeach; ?>
    </nav>

    <div class="side-foot">
      <div style="color:#fff"><?= e(Auth::user()['name'] ?? '') ?></div>
      <div style="color:#8d857a;font-size:.78rem;margin-bottom:.7rem"><?= e(Auth::user()['email'] ?? '') ?></div>
      <a href="<?= url('/') ?>" style="color:#cfc8bd">View storefront →</a>
      <form method="post" action="<?= url('/logout') ?>" style="margin-top:.6rem">
        <?= csrf_field() ?>
        <button class="btn btn-ghost btn-sm" style="color:#cfc8bd;border-color:rgba(255,255,255,.2)">Sign out</button>
      </form>
    </div>
  </aside>

  <main class="admin-main">
    <?= $content ?>
  </main>
</div>

<script src="<?= url('/assets/js/app.js') ?>"></script>
</body>
</html>
