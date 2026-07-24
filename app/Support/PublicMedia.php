<?php

namespace App\Support;

class PublicMedia
{
    public const DISK = 'public';

    /** @var list<string> */
    public const FOLDERS = [
        'categories',
        'classifications',
        'questions',
        'questions/videos',
        'questions/audio',
        'avatars',
    ];

    /**
     * Build a browser URL for a file stored on the public disk.
     * Uses asset() so it works with APP_URL / reverse proxies / subfolders.
     */
    public static function url(?string $path): ?string
    {
        if (! filled($path)) {
            return null;
        }

        $path = str_replace('\\', '/', trim((string) $path));

        if (preg_match('#^https?://#i', $path) === 1) {
            return $path;
        }

        if (str_starts_with($path, '/storage/')) {
            return asset(ltrim($path, '/'));
        }

        if (str_starts_with($path, 'storage/')) {
            return asset($path);
        }

        return asset('storage/'.ltrim($path, '/'));
    }
}
