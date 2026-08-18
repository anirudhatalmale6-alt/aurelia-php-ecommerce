<?php
/** @var array $featured @var array $categories @var array $latest */
$heroTiles = array_slice($featured ?: $latest, 0, 3);
?>
<section class="wrap hero">
  <div>
    <div class="hero-eyebrow">New season · Small batch</div>
    <h1>Considered pieces for the rooms you use every day.</h1>
    <p class="lead">Stoneware, linen and warm light, made in short runs by workshops we visit ourselves.
       Nothing mass-produced, nothing you'll want to replace next year.</p>
    <div class="hero-actions">
      <a class="btn btn-accent" href="<?= url('/shop') ?>">Shop the collection</a>
      <a class="btn btn-ghost" href="<?= url('/shop?sort=price_asc') ?>">Under <?= e(money(40)) ?></a>
    </div>
    <div class="hero-stats">
      <div><strong>14</strong><span>PIECES IN STOCK</span></div>
      <div><strong>2 yr</strong><span>GUARANTEE</span></div>
      <div><strong>48 h</strong><span>DISPATCH</span></div>
    </div>
  </div>

  <div class="hero-art">
    <?php foreach ($heroTiles as $i => $t): ?>
      <a class="tile t<?= $i + 1 ?>" href="<?= url('/product/' . $t['slug']) ?>">
        <img src="<?= e(media($t['image'])) ?>" alt="<?= e($t['name']) ?>">
      </a>
    <?php endforeach; ?>
  </div>
</section>

<section class="wrap section-tight">
  <div class="cat-rail">
    <?php foreach ($categories as $c): ?>
      <a class="cat-card" href="<?= url('/shop?category=' . urlencode($c['slug'])) ?>">
        <div>
          <h3><?= e($c['name']) ?></h3>
          <small><?= e(excerpt($c['description'], 48)) ?></small>
        </div>
        <small><?= (int) $c['product_count'] ?> items →</small>
      </a>
    <?php endforeach; ?>
  </div>
</section>

<section class="wrap section">
  <div class="section-head">
    <div>
      <h2>Featured this month</h2>
      <p>Hand-picked by the studio.</p>
    </div>
    <a class="link-arrow" href="<?= url('/shop') ?>">View everything →</a>
  </div>

  <div class="grid grid-4">
    <?php foreach ($featured as $p): ?><?= product_card($p) ?><?php endforeach; ?>
  </div>
</section>

<section class="wrap section-tight">
  <div class="panel" style="display:grid;grid-template-columns:repeat(3,1fr);gap:2rem;text-align:center">
    <div>
      <h3>Made in small batches</h3>
      <p class="muted" style="margin:0">Short runs from workshops in Portugal, Poland and Sri Lanka.</p>
    </div>
    <div>
      <h3>Free delivery over <?= e(money(75)) ?></h3>
      <p class="muted" style="margin:0">Tracked, carbon-offset shipping on every order.</p>
    </div>
    <div>
      <h3>30-day returns</h3>
      <p class="muted" style="margin:0">Live with it for a month. Send it back if it isn't right.</p>
    </div>
  </div>
</section>

<section class="wrap section">
  <div class="section-head">
    <div><h2>Just arrived</h2><p>The most recent additions to the catalogue.</p></div>
  </div>
  <div class="grid grid-4">
    <?php foreach ($latest as $p): ?><?= product_card($p) ?><?php endforeach; ?>
  </div>
</section>
