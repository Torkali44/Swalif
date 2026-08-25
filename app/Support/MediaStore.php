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

        // Normalize audio extensions (m4a/mp4/mpeg aliases) so browsers can play the file later.
        if (str_starts_with($folder, 'questions/audio') || str_starts_with((string) $mime, 'audio/')) {
            return self::storeBinary($file, $folder, AudioUpload::normalizeExtension($file));
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
        $folder   = trim($folder, '/');
        $pathName = $file->getRealPath() ?: $file->getPathname();

        if (! function_exists('imagecreatefromstring') || ! function_exists('imagejpeg')) {
            return self::storeBinary($file, $folder);
        }

        if (! is_string($pathName) || ! is_file($pathName)) {
            return self::storeBinary($file, $folder);
        }

        // Read source directly from disk path — avoids loading entire file into a PHP string
        $source = @imagecreatefromstring((string) @file_get_contents($pathName));
        if ($source === false) {
            return self::storeBinary($file, $folder);
        }

        $width  = imagesx($source);
        $height = imagesy($source);

        if ($width > $maxWidth) {
            $newWidth  = $maxWidth;
            $newHeight = (int) max(1, round($height * ($maxWidth / $width)));
            $canvas    = imagecreatetruecolor($newWidth, $newHeight);
            if ($canvas === false) {
                imagedestroy($source);
                return self::storeBinary($file, $folder);
            }
            // White background for transparent PNGs
            imagefilledrectangle($canvas, 0, 0, $newWidth, $newHeight,
                imagecolorallocate($canvas, 255, 255, 255));
            imagecopyresampled($canvas, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
            imagedestroy($source);
        } else {
            // No resize needed — use source canvas directly
            $canvas = $source;
        }

        // Write to a temp file on disk (no ob_start memory buffering)
        $tmp = @tempnam(sys_get_temp_dir(), 'mstore_');
        if (! $tmp) {
            imagedestroy($canvas);
            return self::storeBinary($file, $folder);
        }

        imageinterlace($canvas, false); // baseline JPEG — fastest first paint
        $ok = @imagejpeg($canvas, $tmp, 82);
        imagedestroy($canvas);

        if (! $ok || ! is_file($tmp)) {
            @unlink($tmp);
            return self::storeBinary($file, $folder);
        }

        $uuid = Str::uuid()->toString();
        $path = $folder.'/'.$uuid.'.jpg';

        $disk = Storage::disk(PublicMedia::DISK);
        $disk->makeDirectory($folder);
        $dest = $disk->path($path);

        if (@rename($tmp, $dest) || @copy($tmp, $dest)) {
            @unlink($tmp);
            @chmod($dest, 0644);
        } else {
            // Fallback: stream from temp file
            $disk->put($path, (string) file_get_contents($tmp));
            @unlink($tmp);
        }

        return $path;
    }
}
