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
        'characters',
        'letter_grids',
    ];

    /**
     * Build a browser URL for a file on the public disk.
     * Uses a root-relative path so a wrong APP_URL on shared hosting
     * does not break images/logos under /storage/...
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

        $path = preg_replace('#^/?storage/#', '', $path) ?? $path;
        $suffix = 'storage/'.ltrim($path, '/');

        // CLI / queued jobs: fall back to asset()
        if (! app()->runningInConsole() && app()->bound('request') && request()) {
            $base = rtrim((string) request()->getBasePath(), '/');

            return ($base === '' ? '' : $base).'/'.$suffix;
        }

        return asset($suffix);
    }

    /**
     * Absolute filesystem directory where public media is stored (web-accessible).
     */
    public static function rootPath(): string
    {
        return public_path('storage');
    }

    /**
     * Legacy Laravel path (storage/app/public) — used only for one-time migration.
     */
    public static function legacyRootPath(): string
    {
        return storage_path('app/public');
    }
}
