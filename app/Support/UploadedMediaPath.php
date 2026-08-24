<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

class UploadedMediaPath
{
    /**
     * Ensure a client-supplied media path is a real file under questions/.
     */
    public static function isValid(?string $path, string $kind = 'image'): bool
    {
        if (! filled($path)) {
            return false;
        }

        $path = str_replace('\\', '/', trim($path));

        if ($path === '' || str_contains($path, '..') || str_starts_with($path, '/')) {
            return false;
        }

        $pattern = match ($kind) {
            'video' => '#^questions/videos/[A-Za-z0-9._-]+$#',
            'audio' => '#^questions/audio/[A-Za-z0-9._-]+$#',
            default => '#^questions/[A-Za-z0-9._-]+$#',
        };

        if (preg_match($pattern, $path) !== 1) {
            return false;
        }

        return Storage::disk(PublicMedia::DISK)->exists($path);
    }
}
