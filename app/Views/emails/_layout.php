<?php
/** Inline-styled email shell - table layout for maximum client support. */
use App\Core\App;
?>
<!doctype html>
<html>
<body style="margin:0;padding:0;background:#f4f2ee;font-family:Helvetica,Arial,sans-serif;color:#1d1b18">
  <table width="100%" cellpadding="0" cellspacing="0" style="padding:28px 12px">
    <tr><td align="center">
      <table width="100%" cellpadding="0" cellspacing="0" style="max-width:560px;background:#ffffff;border:1px solid #e6e0d8;border-radius:12px">
        <tr>
          <td style="padding:22px 26px;border-bottom:1px solid #e6e0d8">
            <span style="font-size:20px;letter-spacing:.5px"><?= e(App::config('app.name')) ?></span>
          </td>
        </tr>
        <tr><td style="padding:26px;font-size:15px;line-height:1.6">
          <?= $body ?>
        </td></tr>
        <tr>
          <td style="padding:18px 26px;border-top:1px solid #e6e0d8;font-size:12px;color:#8b8378">
            <?= e(App::config('app.tagline')) ?><br>
            You received this because you have an account or placed an order with us.
          </td>
        </tr>
      </table>
    </td></tr>
  </table>
</body>
</html>
