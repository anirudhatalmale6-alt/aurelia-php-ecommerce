<?php
namespace App\Core;

/**
 * Image upload handling for the admin product form.
 *
 * Files are validated by real MIME type (not by the client-supplied name),
 * renamed to a random slug, and re-encoded through GD so that any embedded
 * payload in the original file is discarded.
 */
class Uploader
{
    /** @return array{0:?string,1:?string} [storedFilename, errorMessage] */
    public static function image(array $file, string $prefix = 'product'): array
    {
        $max = App::config('uploads.max_bytes');

        if ($file['error'] !== UPLOAD_ERR_OK) {
            return [null, 'Upload failed (code ' . $file['error'] . ').'];
        }
        if ($file['size'] > $max) {
            return [null, 'Image must be under ' . round($max / 1048576) . ' MB.'];
        }

        $info = @getimagesize($file['tmp_name']);
        if ($info === false) {
            return [null, 'That file is not a readable image.'];
        }
        $mime = $info['mime'];
        if (!in_array($mime, App::config('uploads.mimes'), true)) {
            return [null, 'Only JPEG, PNG or WebP images are accepted.'];
        }

        $source = match ($mime) {
            'image/jpeg' => @imagecreatefromjpeg($file['tmp_name']),
            'image/png'  => @imagecreatefrompng($file['tmp_name']),
            'image/webp' => @imagecreatefromwebp($file['tmp_name']),
            default      => false,
        };
        if (!$source) {
            return [null, 'Image could not be processed.'];
        }

        // Constrain the longest edge so product pages stay fast.
        $resized  = self::constrain($source, 1400);
        $filename = $prefix . '-' . bin2hex(random_bytes(8)) . '.jpg';
        $path     = App::config('uploads.path') . '/' . $filename;

        if (!is_dir(dirname($path))) {
            mkdir(dirname($path), 0775, true);
        }

        $ok = imagejpeg($resized, $path, 86);
        imagedestroy($source);
        if ($resized !== $source) {
            imagedestroy($resized);
        }

        return $ok ? [$filename, null] : [null, 'Image could not be saved.'];
    }

    /** Downscale to fit within $maxEdge, preserving aspect ratio. */
    private static function constrain($image, int $maxEdge)
    {
        $w = imagesx($image);
        $h = imagesy($image);
        if (max($w, $h) <= $maxEdge) {
            return $image;
        }
        $scale = $maxEdge / max($w, $h);
        $nw    = (int) round($w * $scale);
        $nh    = (int) round($h * $scale);

        $canvas = imagecreatetruecolor($nw, $nh);
        imagefill($canvas, 0, 0, imagecolorallocate($canvas, 255, 255, 255));
        imagecopyresampled($canvas, $image, 0, 0, 0, 0, $nw, $nh, $w, $h);
        return $canvas;
    }

    public static function delete(?string $filename): void
    {
        if (!$filename) {
            return;
        }
        $path = App::config('uploads.path') . '/' . basename($filename);
        if (is_file($path)) {
            @unlink($path);
        }
    }
}
