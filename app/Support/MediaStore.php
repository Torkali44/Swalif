<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaStore
{
    /**
     * Store an uploaded file on the public disk.
     * Raster images are resized/compressed with GD when available.
     */
    public static function store(UploadedFile $file, string $folder, int $maxWidth = 1000): string
    {
        $folder = trim($folder, '/');
        $mime = (string) ($file->getMimeType() ?: '');

        $isRasterImage = str_starts_with($mime, 'image/')
            && ! in_array($mime, ['image/svg+xml', 'image/gif'], true);

        if ($isRasterImage) {
            return self::storeImage($file, $folder, $maxWidth);
        }

        return $file->store($folder, PublicMedia::DISK);
    }

    public static function storeImage(UploadedFile $file, string $folder, int $maxWidth = 1000): string
    {
        $folder = trim($folder, '/');

        if (! function_exists('imagecreatefromstring') || ! function_exists('imagejpeg')) {
            return $file->store($folder, PublicMedia::DISK);
        }

        $binary = @file_get_contents($file->getRealPath() ?: $file->getPathname());
        if ($binary === false || $binary === '') {
            return $file->store($folder, PublicMedia::DISK);
        }

        $source = @imagecreatefromstring($binary);
        if ($source === false) {
            return $file->store($folder, PublicMedia::DISK);
        }

        $width = imagesx($source);
        $height = imagesy($source);

        // Always re-encode to a baseline JPEG (avoids progressive "loads band by band")
        $needsResize = $width > $maxWidth;
        $newWidth = $needsResize ? $maxWidth : $width;
        $newHeight = $needsResize
            ? (int) max(1, round($height * ($maxWidth / $width)))
            : $height;

        $canvas = imagecreatetruecolor($newWidth, $newHeight);
        if ($canvas === false) {
            imagedestroy($source);

            return $file->store($folder, PublicMedia::DISK);
        }

        $white = imagecolorallocate($canvas, 255, 255, 255);
        imagefilledrectangle($canvas, 0, 0, $newWidth, $newHeight, $white);
        imagecopyresampled($canvas, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
        imagedestroy($source);

        ob_start();
        imageinterlace($canvas, false);
        imagejpeg($canvas, null, 78);
        $jpeg = ob_get_clean();
        imagedestroy($canvas);

        if (! is_string($jpeg) || $jpeg === '') {
            return $file->store($folder, PublicMedia::DISK);
        }

        $path = $folder.'/'.Str::uuid()->toString().'.jpg';
        Storage::disk(PublicMedia::DISK)->put($path, $jpeg);

        return $path;
    }
}
