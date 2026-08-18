<?php
namespace App\Controllers;

use App\Core\View;

/** Shared controller helpers. */
abstract class Controller
{
    protected function view(string $template, array $data = [], string $layout = 'layouts/shop'): void
    {
        View::render($template, $data, $layout);
    }

    protected function json(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
    }

    protected function back(): void
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? '/';
        header('Location: ' . $referer);
        exit;
    }
}
