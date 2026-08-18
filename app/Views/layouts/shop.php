<?php
/** Storefront layout. @var string $content */
use App\Core\App;
use App\Core\Auth;
use App\Core\Cart;
use App\Core\Flash;

$navCategories = $categories ?? [];
$cartCount     = Cart::count();
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e(($title ?? 'Shop') . ' · ' . App::config('app.name')) ?></title>
<meta name="description" content="<?= e(App::config('app.tagline')) ?>">
<link rel="icon" href="<?= url('/assets/img/favicon.svg') ?>">
<link rel="stylesheet" href="<?= url('/assets/css/app.css') ?>">
</head>
<body>

<div class="flash-area">
<?php foreach (Flash::messages() as $flash): ?>
    <div class="flash <?= $flash['type'] === 'error' ? 'flash-error' : '' ?>"><?= e($flash['message']) ?></div>
<?php endforeach; ?>
</div>

<div class="topbar">Free delivery over <?= e(money(75)) ?> · 30-day returns · Made in small batches</div>

<header class="masthead">
  <div class="wrap masthead-inner">
    <a class="brand" href="<?= url('/') ?>">Aurel<span>ia</span></a>

    <nav class="nav">
      <a href="<?= url('/') ?>" class="<?= trim(active('/')) ?>">Home</a>
      <a href="<?= url('/shop') ?>" class="<?= trim(active('/shop')) ?>">Shop all</a>
      <?php foreach (array_slice($navCategories, 0, 3) as $c): ?>
        <a href="<?= url('/shop?category=' . urlencode($c['slug'])) ?>"><?= e($c['name']) ?></a>
      <?php endforeach; ?>
    </nav>

    <form class="searchbox" action="<?= url('/shop') ?>" method="get" autocomplete="off">
      <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <circle cx="11" cy="11" r="7"/><path d="M20 20l-3.5-3.5"/>
      </svg>
      <input type="search" name="q" id="site-search" placeholder="Search products…"
             value="<?= e($filters['q'] ?? '') ?>">
      <div id="suggest-panel"></div>
    </form>

    <div class="masthead-actions">
      <?php if (Auth::check()): ?>
        <?php if (Auth::isAdmin()): ?>
          <a class="btn btn-ghost btn-sm" href="<?= url('/admin') ?>">Admin</a>
        <?php endif; ?>
        <a href="<?= url('/account') ?>" style="font-size:.93rem">Account</a>
      <?php else: ?>
        <a href="<?= url('/login') ?>" style="font-size:.93rem">Sign in</a>
      <?php endif; ?>

      <a class="cart-link" href="<?= url('/cart') ?>">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
          <path d="M6 7h12l-1 12H7L6 7z"/><path d="M9 7a3 3 0 016 0"/>
        </svg>
        Basket
        <?php if ($cartCount > 0): ?><span class="cart-count"><?= (int) $cartCount ?></span><?php endif; ?>
      </a>
    </div>
  </div>
</header>

<main>
<?= $content ?>
</main>

<footer class="site-foot">
  <div class="wrap">
    <div class="foot-grid">
      <div>
        <a class="brand" href="<?= url('/') ?>">Aurel<span>ia</span></a>
        <p class="muted" style="max-width:34ch;margin-top:.6rem"><?= e(App::config('app.tagline')) ?></p>
      </div>
      <div>
        <h4>Shop</h4>
        <ul>
          <?php foreach (array_slice($navCategories, 0, 4) as $c): ?>
            <li><a href="<?= url('/shop?category=' . urlencode($c['slug'])) ?>"><?= e($c['name']) ?></a></li>
          <?php endforeach; ?>
        </ul>
      </div>
      <div>
        <h4>Help</h4>
        <ul>
          <li><a href="<?= url('/shop') ?>">Delivery</a></li>
          <li><a href="<?= url('/shop') ?>">Returns</a></li>
          <li><a href="<?= url('/account') ?>">Track an order</a></li>
        </ul>
      </div>
      <div>
        <h4>Account</h4>
        <ul>
          <li><a href="<?= url('/login') ?>">Sign in</a></li>
          <li><a href="<?= url('/register') ?>">Create an account</a></li>
          <li><a href="<?= url('/forgot-password') ?>">Reset password</a></li>
        </ul>
      </div>
    </div>
    <div class="foot-base">
      <span>© <?= date('Y') ?> <?= e(App::config('app.name')) ?>. Demo build.</span>
      <span>Payments by <?= e(\App\Core\Payments\PaymentManager::gateway()->name()) ?></span>
    </div>
  </div>
</footer>

<script src="<?= url('/assets/js/app.js') ?>"></script>
</body>
</html>
