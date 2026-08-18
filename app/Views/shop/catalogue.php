<?php
/** @var array $result @var array $filters @var array $categories @var ?array $category */
$rows = $result['rows'];

/** Rebuild the current query string with one value replaced. */
$qs = function (array $overrides) use ($filters): string {
    $params = array_filter([
        'category'  => $filters['category'],
        'q'         => $filters['q'],
        'sort'      => $filters['sort'],
        'max_price' => $filters['max_price'],
        'in_stock'  => $filters['in_stock'] ? 1 : null,
    ], fn($v) => $v !== '' && $v !== null && $v !== 0);
    $params = array_merge($params, $overrides);
    $params = array_filter($params, fn($v) => $v !== '' && $v !== null);
    return $params ? '?' . http_build_query($params) : '';
};
?>
<div class="wrap section-tight">
  <p class="crumbs"><a href="<?= url('/') ?>">Home</a> / <?= e($category['name'] ?? 'Shop all') ?></p>
  <h1 style="margin-bottom:.2rem"><?= e($category['name'] ?? ($filters['q'] ? 'Results for “' . $filters['q'] . '”' : 'Shop all')) ?></h1>
  <p class="muted"><?= e($category['description'] ?? 'Every piece in the current collection.') ?></p>
</div>

<div class="wrap catalogue" style="padding-bottom:4rem">
  <aside class="filters">
    <h4>Categories</h4>
    <ul>
      <li><a href="<?= url('/shop' . $qs(['category' => null])) ?>" class="<?= $filters['category'] === '' ? 'is-on' : '' ?>">All products</a></li>
      <?php foreach ($categories as $c): ?>
        <li>
          <a href="<?= url('/shop' . $qs(['category' => $c['slug']])) ?>"
             class="<?= $filters['category'] === $c['slug'] ? 'is-on' : '' ?>">
            <span><?= e($c['name']) ?></span><span class="faint"><?= (int) $c['product_count'] ?></span>
          </a>
        </li>
      <?php endforeach; ?>
    </ul>

    <form method="get" action="<?= url('/shop') ?>">
      <?php if ($filters['category']): ?>
        <input type="hidden" name="category" value="<?= e($filters['category']) ?>">
      <?php endif; ?>
      <?php if ($filters['q']): ?>
        <input type="hidden" name="q" value="<?= e($filters['q']) ?>">
      <?php endif; ?>

      <h4>Max price</h4>
      <label class="field">
        <input class="input" type="number" name="max_price" min="0" step="1"
               placeholder="Any" value="<?= e($filters['max_price']) ?>">
      </label>

      <h4>Availability</h4>
      <label class="checkline">
        <input type="checkbox" name="in_stock" value="1" <?= $filters['in_stock'] ? 'checked' : '' ?>>
        In stock only
      </label>

      <button class="btn btn-sm btn-block">Apply filters</button>
      <?php if ($filters['max_price'] || $filters['in_stock']): ?>
        <a class="faint" style="display:block;text-align:center;margin-top:.6rem"
           href="<?= url('/shop' . $qs(['max_price' => null, 'in_stock' => null])) ?>">Clear</a>
      <?php endif; ?>
    </form>
  </aside>

  <div>
    <div class="toolbar">
      <span class="muted"><?= (int) $result['total'] ?> product<?= $result['total'] === 1 ? '' : 's' ?></span>
      <form method="get" action="<?= url('/shop') ?>" class="row">
        <?php foreach (['category', 'q', 'max_price'] as $k): ?>
          <?php if (!empty($filters[$k])): ?>
            <input type="hidden" name="<?= e($k) ?>" value="<?= e($filters[$k]) ?>">
          <?php endif; ?>
        <?php endforeach; ?>
        <?php if ($filters['in_stock']): ?><input type="hidden" name="in_stock" value="1"><?php endif; ?>
        <label class="faint" for="sort">Sort</label>
        <select class="input" name="sort" id="sort" onchange="this.form.submit()" style="width:auto">
          <option value="">Newest</option>
          <option value="price_asc"  <?= $filters['sort'] === 'price_asc'  ? 'selected' : '' ?>>Price: low to high</option>
          <option value="price_desc" <?= $filters['sort'] === 'price_desc' ? 'selected' : '' ?>>Price: high to low</option>
          <option value="name"       <?= $filters['sort'] === 'name'       ? 'selected' : '' ?>>Name A–Z</option>
        </select>
      </form>
    </div>

    <?php if (!$rows): ?>
      <div class="panel center" style="padding:3rem 1.5rem">
        <h3>Nothing matched that search</h3>
        <p class="muted">Try a different keyword, or browse the full collection.</p>
        <a class="btn btn-ghost" href="<?= url('/shop') ?>">Show all products</a>
      </div>
    <?php else: ?>
      <div class="grid grid-3">
        <?php foreach ($rows as $p): ?><?= product_card($p) ?><?php endforeach; ?>
      </div>

      <?php if ($result['pages'] > 1): ?>
        <nav class="pagination">
          <?php for ($i = 1; $i <= $result['pages']; $i++): ?>
            <?php if ($i === $result['page']): ?>
              <span class="is-current"><?= $i ?></span>
            <?php else: ?>
              <a href="<?= url('/shop' . $qs(['page' => $i])) ?>"><?= $i ?></a>
            <?php endif; ?>
          <?php endfor; ?>
        </nav>
      <?php endif; ?>
    <?php endif; ?>
  </div>
</div>
