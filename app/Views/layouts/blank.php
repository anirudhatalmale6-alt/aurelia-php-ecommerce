<?php
/** Minimal chrome-free layout (payment sandbox). @var string $content */
use App\Core\App;
use App\Core\Flash;
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e(($title ?? '') . ' · ' . App::config('app.name')) ?></title>
<link rel="icon" href="<?= url('/assets/img/favicon.svg') ?>">
<link rel="stylesheet" href="<?= url('/assets/css/app.css') ?>">
</head>
<body>
<div class="flash-area">
<?php foreach (Flash::messages() as $flash): ?>
  <div class="flash <?= $flash['type'] === 'error' ? 'flash-error' : '' ?>"><?= e($flash['message']) ?></div>
<?php endforeach; ?>
</div>
<?= $content ?>
</body>
</html>
