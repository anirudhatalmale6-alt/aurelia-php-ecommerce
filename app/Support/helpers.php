<?php
/**
 * Global template helpers. Loaded once from public/index.php.
 */

use App\Core\App;
use App\Core\Csrf;
use App\Core\Auth;

/** Escape a value for HTML output. */
function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Format a decimal as a display price. */
function money($amount): string
{
    return App::currency((float) $amount);
}

/** Absolute URL for a path within the app. */
function url(string $path = ''): string
{
    return App::url($path);
}

/** URL for a product image (falls back to a generated placeholder). */
function media(?string $file): string
{
    if (!$file) {
        return url('/assets/img/placeholder.svg');
    }
    if (str_starts_with($file, 'http')) {
        return $file;
    }
    return url('/media/' . ltrim($file, '/'));
}

/** Hidden CSRF input for forms. */
function csrf_field(): string
{
    return '<input type="hidden" name="_token" value="' . e(Csrf::token()) . '">';
}

/** Repopulate a form field after a validation failure. */
function old(string $key, $default = ''): string
{
    return e(\App\Core\Flash::old($key, $default));
}

/** Validation error message for a field, or null. */
function error(string $field): ?string
{
    return \App\Core\Flash::error($field);
}

/** Currently authenticated user (or null). */
function user(): ?array
{
    return Auth::user();
}

/** Mark the nav item for the current section as active. */
function active(string $prefix): string
{
    $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    if ($prefix === '/') {
        return $path === '/' ? ' is-active' : '';
    }
    return str_starts_with($path, $prefix) ? ' is-active' : '';
}

/** Redirect and stop. */
function redirect(string $path): void
{
    header('Location: ' . (str_starts_with($path, 'http') ? $path : url($path)));
    exit;
}

/** Render one product card (shop/_card.php). */
function product_card(array $product): string
{
    return \App\Core\View::partial('shop/_card', ['p' => $product]);
}

/** Truncate a string on a word boundary. */
function excerpt(?string $text, int $limit = 120): string
{
    $text = trim((string) $text);
    if (mb_strlen($text) <= $limit) {
        return $text;
    }
    return rtrim(mb_substr($text, 0, $limit), " ,.;:") . '…';
}
