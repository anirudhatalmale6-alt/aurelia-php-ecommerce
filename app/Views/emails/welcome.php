<?php
/** @var string $name */
use App\Core\View;

ob_start(); ?>
<h2 style="margin:0 0 12px;font-size:20px">Welcome, <?= e(explode(' ', $name)[0]) ?>.</h2>
<p style="margin:0 0 16px">Your account is ready. You can now check out faster and follow your orders from one place.</p>
<a href="<?= url('/shop') ?>"
   style="display:inline-block;background:#a84f2e;color:#ffffff;text-decoration:none;padding:11px 20px;border-radius:999px">
  Start browsing
</a>
<?php $body = ob_get_clean();
echo View::partial('emails/_layout', ['body' => $body]);
