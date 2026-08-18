<?php
/**
 * Creates the schema and loads demo content.
 *
 *   php database/seed.php            # keeps existing data
 *   php database/seed.php --fresh    # drops and rebuilds everything
 *
 * Works against both drivers: DB_DRIVER=sqlite (default) or DB_DRIVER=mysql.
 */

declare(strict_types=1);

$root = dirname(__DIR__);

spl_autoload_register(function (string $class) use ($root): void {
    if (!str_starts_with($class, 'App\\')) {
        return;
    }
    $path = $root . '/app/' . str_replace('\\', '/', substr($class, 4)) . '.php';
    if (is_file($path)) {
        require $path;
    }
});

$config = require $root . '/config/config.php';
require $root . '/app/Support/helpers.php';

use App\Core\App;
use App\Core\Database;

App::boot($config);

$fresh  = in_array('--fresh', $argv, true);
$driver = App::config('db.driver');

if ($driver === 'sqlite') {
    $file = App::config('db.sqlite');
    if ($fresh && is_file($file)) {
        unlink($file);
    }
    if (!is_dir(dirname($file))) {
        mkdir(dirname($file), 0775, true);
    }
    touch($file);
}

$db  = Database::instance();
$pdo = $db->pdo();

if ($fresh && $driver !== 'sqlite') {
    $pdo->exec('SET FOREIGN_KEY_CHECKS = 0');
    foreach (['order_items', 'orders', 'password_resets', 'products', 'categories', 'users'] as $table) {
        $pdo->exec("DROP TABLE IF EXISTS $table");
    }
    $pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
}

$schema = file_get_contents($root . '/database/schema.' . ($driver === 'sqlite' ? 'sqlite' : 'mysql') . '.sql');
foreach (array_filter(array_map('trim', explode(';', $schema))) as $statement) {
    $pdo->exec($statement);
}
echo "Schema ready ($driver).\n";

if ((int) $db->scalar('SELECT COUNT(*) FROM users') > 0 && !$fresh) {
    echo "Data already present - nothing seeded. Use --fresh to rebuild.\n";
    exit(0);
}

/* ---------------------------------------------------------------- accounts */
$now = date('Y-m-d H:i:s');

$db->insert('users', [
    'name'          => 'Store Admin',
    'email'         => 'admin@aurelia.test',
    'password_hash' => password_hash('AdminPass123', PASSWORD_DEFAULT),
    'role'          => 'admin',
    'created_at'    => $now,
]);

$customerId = $db->insert('users', [
    'name'          => 'Nadia Fernandes',
    'email'         => 'customer@aurelia.test',
    'phone'         => '+1 555 0142',
    'password_hash' => password_hash('CustomerPass123', PASSWORD_DEFAULT),
    'role'          => 'customer',
    'created_at'    => $now,
]);

/* -------------------------------------------------------------- categories */
$categories = [
    ['Kitchen & Dining', 'Stoneware, glass and wood for the table.', 1, ['#c08155', '#f0dcc9'], 'bowl'],
    ['Home Textiles',    'Linen, wool and cotton woven to last.',    2, ['#7d8f80', '#dfe6de'], 'textile'],
    ['Lighting',         'Warm, considered light for every room.',   3, ['#b08a4a', '#eee3cf'], 'lamp'],
    ['Desk & Paper',     'Tools for writing, planning and making.',  4, ['#6a6f83', '#dfe1e8'], 'paper'],
];

$categoryIds = [];
foreach ($categories as [$name, $description, $position, $palette, $shape]) {
    $categoryIds[$name] = $db->insert('categories', [
        'name'        => $name,
        'slug'        => trim(preg_replace('/[^a-z0-9]+/', '-', mb_strtolower($name)), '-'),
        'description' => $description,
        'position'    => $position,
    ]);
}

/* ---------------------------------------------------------------- products */
$products = [
    ['Kitchen & Dining', 'Terra Stoneware Bowl Set',   48.00,  0,     24, 1, 'Four hand-glazed bowls, kiln-fired in small batches.'],
    ['Kitchen & Dining', 'Olivewood Serving Board',    64.00,  52.00, 12, 1, 'Single-piece olivewood, oiled and ready for the table.'],
    ['Kitchen & Dining', 'Ridged Glass Carafe',        38.00,  0,     31, 0, 'Mouth-blown borosilicate that holds a litre.'],
    ['Kitchen & Dining', 'Matte Ceramic Mug',          22.00,  0,      4, 0, 'A heavy-bottomed mug that keeps coffee hot.'],
    ['Home Textiles',    'Washed Linen Throw',         95.00,  0,      9, 1, 'Stonewashed European flax, softer with every wash.'],
    ['Home Textiles',    'Wool Cushion Cover',         42.00,  34.00, 18, 0, 'Undyed lambswool with a hidden zip.'],
    ['Home Textiles',    'Waffle Cotton Towel',        28.00,  0,     40, 0, 'Quick-drying waffle weave in a deep sand tone.'],
    ['Lighting',         'Paper Pendant Shade',        86.00,  0,      7, 1, 'Hand-folded paper that diffuses light evenly.'],
    ['Lighting',         'Brass Table Lamp',          148.00, 129.00,  5, 1, 'Solid brass with a dimmable warm-white bulb.'],
    ['Lighting',         'Beeswax Taper Set',          19.00,  0,     52, 0, 'Six pure beeswax tapers, six hours each.'],
    ['Desk & Paper',     'Bound Notebook A5',          18.00,  0,     64, 1, 'Sewn binding, 160 pages of 100gsm cream paper.'],
    ['Desk & Paper',     'Machined Pen',               54.00,  0,     16, 0, 'Turned aluminium body with a refillable core.'],
    ['Desk & Paper',     'Blackwood Desk Tray',        72.00,  58.00,  3, 1, 'Chamfered edges, felt-lined base.'],
    ['Desk & Paper',     'Cotton Card Set',            14.00,  0,     45, 0, 'Ten letterpress cards with kraft envelopes.'],
];

$imageDir = App::config('uploads.path');
if (!is_dir($imageDir)) {
    mkdir($imageDir, 0775, true);
}

/**
 * Generate a placeholder product tile so the demo ships with no external
 * assets. Each category gets its own palette and a simple object silhouette -
 * these are swapped for the client's real photography via the admin panel.
 */
function makeTile(string $file, array $palette, string $shape): void
{
    [$from, $to] = $palette;
    $w = 900;
    $h = 900;
    $img = imagecreatetruecolor($w, $h);

    $hex = fn(string $c) => [
        (int) hexdec(substr($c, 1, 2)),
        (int) hexdec(substr($c, 3, 2)),
        (int) hexdec(substr($c, 5, 2)),
    ];
    [$r1, $g1, $b1] = $hex($from);   // object tone
    [$r2, $g2, $b2] = $hex($to);     // backdrop tone

    /** Mix a colour towards white by $amount (0-1). */
    $lighten = function (array $rgb, float $amount) use ($img) {
        return imagecolorallocate(
            $img,
            (int) round($rgb[0] + (255 - $rgb[0]) * $amount),
            (int) round($rgb[1] + (255 - $rgb[1]) * $amount),
            (int) round($rgb[2] + (255 - $rgb[2]) * $amount)
        );
    };

    // Studio backdrop: vertical gradient from a pale wash to near-white.
    for ($y = 0; $y < $h; $y++) {
        $t = $y / $h;
        imagefilledrectangle($img, 0, $y, $w, $y + 1, imagecolorallocate(
            $img,
            (int) round($r2 + (252 - $r2) * (1 - $t * .85)),
            (int) round($g2 + (250 - $g2) * (1 - $t * .85)),
            (int) round($b2 + (247 - $b2) * (1 - $t * .85))
        ));
    }

    // Contact shadow under the object.
    $shadow = imagecolorallocatealpha($img, $r1, $g1, $b1, 100);
    imagefilledellipse($img, 450, 660, 460, 70, $shadow);

    $body      = imagecolorallocate($img, $r1, $g1, $b1);
    $highlight = $lighten([$r1, $g1, $b1], .28);
    $shade     = imagecolorallocate($img, (int) ($r1 * .78), (int) ($g1 * .78), (int) ($b1 * .78));

    switch ($shape) {
        case 'bowl':
            imagefilledarc($img, 450, 470, 420, 380, 0, 180, $body, IMG_ARC_PIE);
            imagefilledellipse($img, 450, 470, 420, 120, $highlight);
            imagefilledellipse($img, 450, 472, 360, 90, $shade);
            break;

        case 'textile':
            // Folded stack of cloth.
            imagefilledrectangle($img, 250, 430, 650, 520, $body);
            imagefilledrectangle($img, 275, 520, 625, 600, $shade);
            imagefilledrectangle($img, 300, 380, 600, 430, $highlight);
            break;

        case 'lamp':
            // Conical shade, stem and base.
            imagefilledpolygon($img, [330, 430, 570, 430, 620, 300, 280, 300], $body);
            imagefilledrectangle($img, 435, 430, 465, 610, $shade);
            imagefilledellipse($img, 450, 620, 240, 56, $highlight);
            break;

        default: // 'paper' - a bound notebook
            imagefilledrectangle($img, 300, 280, 620, 640, $body);
            imagefilledrectangle($img, 300, 280, 348, 640, $shade);
            imagefilledrectangle($img, 380, 340, 580, 352, $highlight);
            imagefilledrectangle($img, 380, 384, 540, 396, $highlight);
            break;
    }

    imagejpeg($img, $file, 88);
    imagedestroy($img);
}

$artByCategory = [];
foreach ($categories as [$name, , , $palette, $shape]) {
    $artByCategory[$name] = [$palette, $shape];
}

$created = 0;
foreach ($products as $i => [$category, $name, $price, $sale, $stock, $featured, $blurb]) {
    $slug     = trim(preg_replace('/[^a-z0-9]+/', '-', mb_strtolower($name)), '-');
    $filename = 'seed-' . $slug . '.jpg';
    makeTile($imageDir . '/' . $filename, $artByCategory[$category][0], $artByCategory[$category][1]);

    $db->insert('products', [
        'category_id'       => $categoryIds[$category],
        'name'              => $name,
        'slug'              => $slug,
        'sku'               => 'AUR-' . str_pad((string) ($i + 1), 4, '0', STR_PAD_LEFT),
        'short_description' => $blurb,
        'description'       => $blurb . "\n\nEvery piece is checked by hand before it leaves the studio. "
            . "Ships in recycled packaging within two working days, and comes with a two-year guarantee "
            . "against manufacturing faults.",
        'price'             => $price,
        'sale_price'        => $sale,
        'stock'             => $stock,
        'image'             => $filename,
        'is_active'         => 1,
        'is_featured'       => $featured,
        'created_at'        => date('Y-m-d H:i:s', strtotime("-$i days")),
        'updated_at'        => $now,
    ]);
    $created++;
}

/* ------------------------------------------------------- a settled order */
$orderId = $db->insert('orders', [
    'reference'         => 'AUR-' . date('ymd') . '-DEMO01',
    'user_id'           => $customerId,
    'customer_name'     => 'Nadia Fernandes',
    'email'             => 'customer@aurelia.test',
    'phone'             => '+1 555 0142',
    'address_line1'     => '14 Harbour Lane',
    'city'              => 'Lisbon',
    'postcode'          => '1100-021',
    'country'           => 'Portugal',
    'subtotal'          => 143.00,
    'shipping'          => 0.00,
    'tax'               => 0.00,
    'total'             => 143.00,
    'status'            => 'shipped',
    'payment_method'    => 'Demo (sandbox)',
    'payment_reference' => 'demo_seeded_reference',
    'paid_at'           => date('Y-m-d H:i:s', strtotime('-3 days')),
    'created_at'        => date('Y-m-d H:i:s', strtotime('-3 days')),
    'updated_at'        => date('Y-m-d H:i:s', strtotime('-1 day')),
]);

$db->insert('order_items', [
    'order_id'     => $orderId,
    'product_id'   => 1,
    'product_name' => 'Terra Stoneware Bowl Set',
    'sku'          => 'AUR-0001',
    'unit_price'   => 48.00,
    'quantity'     => 1,
    'line_total'   => 48.00,
]);
$db->insert('order_items', [
    'order_id'     => $orderId,
    'product_id'   => 5,
    'product_name' => 'Washed Linen Throw',
    'sku'          => 'AUR-0005',
    'unit_price'   => 95.00,
    'quantity'     => 1,
    'line_total'   => 95.00,
]);

echo "Seeded {$created} products, 4 categories, 2 accounts and 1 sample order.\n";
echo "Admin:    admin@aurelia.test / AdminPass123\n";
echo "Customer: customer@aurelia.test / CustomerPass123\n";
