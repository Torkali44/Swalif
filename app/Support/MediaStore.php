<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaStore
{
    /**
     * Store an uploaded file on the public disk.
     * Raster images are always re-encoded as baseline JPEG (no progressive banding).
     * Video/audio use a fast temp→disk move (no stream copy).
     */
    public static function store(UploadedFile $file, string $folder, int $maxWidth = 1000): string
    {
        $folder = trim($folder, '/');
        $mime = (string) ($file->getMimeType() ?: '');

        $isRasterImage = str_starts_with($mime, 'image/')
            && ! in_array($mime, ['image/svg+xml', 'image/gif'], true);

        // Some hosts report empty/octet-stream for images — still try GD by extension
        if (! $isRasterImage) {
            $ext = strtolower((string) ($file->getClientOriginalExtension() ?: ''));
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'bmp'], true)) {
                $isRasterImage = true;
            }
        }

        if ($isRasterImage) {
            return self::storeImage($file, $folder, $maxWidth);
        }

        return self::storeBinary($file, $folder);
    }

    /**
     * Fast path: rename/move the PHP temp file into public storage.
     */
    public static function storeBinary(UploadedFile $file, string $folder, ?string $extension = null): string
    {
        $folder = trim($folder, '/');
        $ext = $extension ?: strtolower((string) ($file->getClientOriginalExtension() ?: $file->extension() ?: 'bin'));
        $ext = preg_replace('/[^a-z0-9]/', '', $ext) ?: 'bin';
        $name = Str::uuid()->toString().'.'.$ext;
        $relative = $folder.'/'.$name;

        $disk = Storage::disk(PublicMedia::DISK);
        $disk->makeDirectory($folder);
        $destination = $disk->path($relative);

        $source = $file->getRealPath() ?: $file->getPathname();

        if (is_string($source) && $source !== '' && is_file($source)) {
            if (is_uploaded_file($source) && @move_uploaded_file($source, $destination)) {
                @chmod($destination, 0644);

                return $relative;
            }

            if (@rename($source, $destination)) {
                @chmod($destination, 0644);

                return $relative;
            }

            if (@copy($source, $destination)) {
                @chmod($destination, 0644);
                @unlink($source);

                return $relative;
            }
        }

        return $file->storeAs($folder, $name, PublicMedia::DISK);
    }

    public static function storeImage(UploadedFile $file, string $folder, int $maxWidth = 1000): string
    {
        $folder = trim($folder, '/');
        $pathName = $file->getRealPath() ?: $file->getPathname();

        if (! function_exists('imagecreatefromstring') || ! function_exists('imagejpeg')) {
            return self::storeBinary($file, $folder);
        }

        $binary = is_string($pathName) && is_file($pathName)
            ? @file_get_contents($pathName)
            : false;

        if ($binary === false || $binary === '') {
            return self::storeBinary($file, $folder);
        }

        $source = @imagecreatefromstring($binary);
        unset($binary);
        if ($source === false) {
            return self::storeBinary($file, $folder);
        }

        $width = imagesx($source);
        $height = imagesy($source);

        $needsResize = $width > $maxWidth;
        $newWidth = $needsResize ? $maxWidth : $width;
        $newHeight = $needsResize
            ? (int) max(1, round($height * ($maxWidth / $width)))
            : $height;

        $canvas = imagecreatetruecolor($newWidth, $newHeight);
        if ($canvas === false) {
            imagedestroy($source);

            return self::storeBinary($file, $folder);
        }

        $white = imagecolorallocate($canvas, 255, 255, 255);
        imagefilledrectangle($canvas, 0, 0, $newWidth, $newHeight, $white);
        imagecopyresampled($canvas, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
        imagedestroy($source);

        // Always baseline JPEG — progressive files paint band-by-band and feel "slow"
        ob_start();
        imageinterlace($canvas, false);
        imagejpeg($canvas, null, 72);
        $jpeg = ob_get_clean();
        imagedestroy($canvas);

        if (! is_string($jpeg) || $jpeg === '') {
            return self::storeBinary($file, $folder);
        }

        $path = $folder.'/'.Str::uuid()->toString().'.jpg';
        Storage::disk(PublicMedia::DISK)->put($path, $jpeg);

        return $path;
    }
}
