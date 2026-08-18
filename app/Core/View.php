<?php
namespace App\Core;

/**
 * Plain-PHP template renderer.
 *
 * Views live in app/Views and are wrapped by a layout. Everything printed with
 * e() is HTML-escaped, so template output is safe by default.
 */
class View
{
    public static function render(string $template, array $data = [], string $layout = 'layouts/shop'): void
    {
        $content = self::capture($template, $data);
        $data['content'] = $content;
        echo self::capture($layout, $data);
    }

    /** Render a template without a layout (used for partials / AJAX). */
    public static function partial(string $template, array $data = []): string
    {
        return self::capture($template, $data);
    }

    private static function capture(string $template, array $data): string
    {
        $file = App::config('app.root') . '/app/Views/' . $template . '.php';
        if (!is_file($file)) {
            throw new \RuntimeException("View not found: {$template}");
        }
        extract($data, EXTR_SKIP);
        ob_start();
        include $file;
        return (string) ob_get_clean();
    }
}
