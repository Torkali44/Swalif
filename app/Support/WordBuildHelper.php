<?php

namespace App\Support;

class WordBuildHelper
{
    /** Arabic diacritics, tatweel, and zero-width marks to strip. */
    private const STRIP_PATTERN = '/[\x{064B}-\x{065F}\x{0670}\x{0640}\x{200C}\x{200D}\x{200E}\x{200F}\x{FEFF}]/u';

    /** Alef variants normalized to bare alef. */
    private const ALEF_VARIANTS = ['أ', 'إ', 'آ', 'ٱ', 'ٲ', 'ٳ', 'ٵ'];

    public static function normalizeLetter(string $char): string
    {
        $char = preg_replace(self::STRIP_PATTERN, '', trim($char)) ?? '';
        $char = str_replace(self::ALEF_VARIANTS, 'ا', $char);
        $char = str_replace(['ى', 'ئ'], 'ي', $char);
        $char = str_replace('ؤ', 'و', $char);

        return $char;
    }

    public static function normalizeWord(string $word): string
    {
        $word = preg_replace('/\s+/u', '', trim($word)) ?? '';
        $word = preg_replace(self::STRIP_PATTERN, '', $word) ?? '';

        $chars = self::splitChars($word);
        $normalized = '';
        foreach ($chars as $char) {
            $normalized .= self::normalizeLetter($char);
        }

        return $normalized;
    }

    /**
     * هل وجد اللاعب كل الكلمات المطلوبة؟
     *
     * @param  array<int, string>  $validWords
     */
    public static function foundAllWords(array $validWords, ?string $playerAnswer): bool
    {
        if ($playerAnswer === null || trim($playerAnswer) === '') {
            return false;
        }

        $decoded = json_decode($playerAnswer, true);
        $foundRaw = is_array($decoded)
            ? $decoded
            : (preg_split('/\s*[,،]\s*/u', $playerAnswer) ?: []);

        $found = collect($foundRaw)
            ->map(fn ($word) => self::normalizeWord((string) $word))
            ->filter(fn ($word) => $word !== '')
            ->unique()
            ->values();

        $required = collect($validWords)
            ->map(fn ($word) => self::normalizeWord((string) $word))
            ->filter(fn ($word) => $word !== '')
            ->unique()
            ->values();

        if ($required->isEmpty()) {
            return false;
        }

        return $required->every(fn ($word) => $found->contains($word));
    }

    /**
     * @param  array<int, string>  $letters
     * @return array<string, int>
     */
    public static function letterFrequency(array $letters): array
    {
        $freq = [];

        foreach ($letters as $letter) {
            $normalized = self::normalizeLetter((string) $letter);
            if ($normalized === '') {
                continue;
            }
            $freq[$normalized] = ($freq[$normalized] ?? 0) + 1;
        }

        return $freq;
    }

    /**
     * @param  array<int, string>  $availableLetters
     */
    public static function canFormWord(array $availableLetters, string $word): bool
    {
        $bag = self::letterFrequency($availableLetters);
        $wordChars = self::splitChars(self::normalizeWord($word));

        if ($wordChars === []) {
            return false;
        }

        foreach ($wordChars as $char) {
            $normalized = self::normalizeLetter($char);
            if ($normalized === '' || ! isset($bag[$normalized]) || $bag[$normalized] <= 0) {
                return false;
            }
            $bag[$normalized]--;
        }

        return true;
    }

    /**
     * @param  array<int, string>  $words
     * @return array<int, string>
     */
    public static function uniqueWords(array $words): array
    {
        $seen = [];

        return collect($words)
            ->map(fn ($word) => self::normalizeWord((string) $word))
            ->filter(fn ($word) => $word !== '')
            ->filter(function ($word) use (&$seen) {
                $key = mb_strtolower($word);
                if (isset($seen[$key])) {
                    return false;
                }
                $seen[$key] = true;

                return true;
            })
            ->values()
            ->all();
    }

    /**
     * @return array<int, string>
     */
    private static function splitChars(string $text): array
    {
        if ($text === '') {
            return [];
        }

        return preg_split('//u', $text, -1, PREG_SPLIT_NO_EMPTY) ?: [];
    }
}
