<?php
namespace App\Controllers;

use App\Core\App;
use App\Core\Request;

/**
 * Serves uploaded product images from storage/ (outside the web root).
 *
 * basename() strips any path component, so "../../config/config.php" cannot
 * escape the uploads directory.
 */
class MediaController extends Controller
{
    public function show(Request $request, string $file): void
    {
        $path = App::config('uploads.path') . '/' . basename($file);

        if (!is_file($path)) {
            http_response_code(404);
            echo 'Not found';
            return;
        }

        $mime = mime_content_type($path) ?: 'application/octet-stream';
        if (!str_starts_with($mime, 'image/')) {
            http_response_code(404);
            echo 'Not found';
            return;
        }

        header('Content-Type: ' . $mime);
        header('Content-Length: ' . filesize($path));
        header('Cache-Control: public, max-age=2592000');
        readfile($path);
    }
}
