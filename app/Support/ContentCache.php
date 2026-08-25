<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;

class ContentCache
{
    /**
     * Catalog keys used on public pages (home, categories, custom game).
     * Must be cleared whenever categories / classifications / questions change.
     */
    public static function catalogKeys(): array
    {
        return [
            'home.active_categories',
            'categories.active_ordered',
            'categories.active_classifications',
            'classifications.active_ordered',
        ];
    }

    public static function flushCatalog(): void
    {
        foreach (self::catalogKeys() as $key) {
            Cache::forget($key);
        }
    }
}
