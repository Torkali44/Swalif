<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;

class AudioUpload
{
    /** Extensions accepted for question audio (aliases included). */
    public static function allowedExtensions(): array
    {
        return ['mp3', 'wav', 'ogg', 'oga', 'm4a', 'aac', 'opus', 'webm', 'mp4', 'mpeg', 'mpga'];
    }

    /** Laravel validation rule fragment: extensions:... */
    public static function extensionsRule(): string
    {
        return 'extensions:'.implode(',', self::allowedExtensions());
    }

    public static function maxKilobytes(): int
    {
        // Keep under typical shared-hosting post_max (40M)
        return 40960;
    }

    public static function acceptAttribute(): string
    {
        return 'audio/mpeg,audio/mp3,audio/wav,audio/x-wav,audio/ogg,audio/webm,audio/mp4,audio/x-m4a,audio/aac,audio/opus,.mp3,.wav,.ogg,.oga,.m4a,.aac,.opus,.webm,.mp4,.mpeg';
    }

    public static function humanFormats(): string
    {
        return 'mp3 / wav / ogg / m4a / aac / opus / webm';
    }

    /**
     * Pick a stable playback-friendly extension from mime + original name.
     * iPhone/WhatsApp often send m4a labeled as mp4 or audio/mp4.
     */
    public static function normalizeExtension(UploadedFile $file): string
    {
        $ext = strtolower((string) ($file->getClientOriginalExtension() ?: ''));
        $mime = strtolower((string) ($file->getMimeType() ?: ''));

        $mimeMap = [
            'audio/mpeg' => 'mp3',
            'audio/mp3' => 'mp3',
            'audio/x-mpeg' => 'mp3',
            'audio/mpeg3' => 'mp3',
            'audio/wav' => 'wav',
            'audio/x-wav' => 'wav',
            'audio/wave' => 'wav',
            'audio/ogg' => 'ogg',
            'application/ogg' => 'ogg',
            'audio/opus' => 'opus',
            'audio/webm' => 'webm',
            'audio/mp4' => 'm4a',
            'audio/x-m4a' => 'm4a',
            'audio/m4a' => 'm4a',
            'audio/aac' => 'aac',
            'audio/aacp' => 'aac',
            'audio/x-aac' => 'aac',
        ];

        if (isset($mimeMap[$mime])) {
            return $mimeMap[$mime];
        }

        $aliases = [
            'mpga' => 'mp3',
            'mpeg' => 'mp3',
            'oga' => 'ogg',
            'mp4' => 'm4a',
        ];

        if (isset($aliases[$ext])) {
            return $aliases[$ext];
        }

        if (in_array($ext, self::allowedExtensions(), true)) {
            return $ext;
        }

        return 'mp3';
    }

    public static function isAllowed(UploadedFile $file): bool
    {
        $ext = strtolower((string) ($file->getClientOriginalExtension() ?: ''));
        $normalized = self::normalizeExtension($file);

        return in_array($ext, self::allowedExtensions(), true)
            || in_array($normalized, ['mp3', 'wav', 'ogg', 'm4a', 'aac', 'opus', 'webm'], true);
    }
}
