<?php
/** @var string $link */
use App\Core\View;

ob_start(); ?>
<h2 style="margin:0 0 12px;font-size:20px">Reset your password</h2>
<p style="margin:0 0 16px">Click the button below to choose a new password. The link works once and expires in one hour.</p>
<a href="<?= e($link) ?>"
   style="display:inline-block;background:#a84f2e;color:#ffffff;text-decoration:none;padding:11px 20px;border-radius:999px">
  Set a new password
</a>
<p style="margin:20px 0 0;color:#8b8378;font-size:13px">
  If you didn't ask for this, you can ignore the email — your password stays as it is.
</p>
<p style="margin:12px 0 0;color:#8b8378;font-size:12px;word-break:break-all"><?= e($link) ?></p>
<?php $body = ob_get_clean();
echo View::partial('emails/_layout', ['body' => $body]);
