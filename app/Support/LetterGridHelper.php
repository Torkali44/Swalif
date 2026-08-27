<?php

namespace App\Support;

class LetterGridHelper
{
    public static function answerStartsWithLetter(string $answer, string $letter): bool
    {
        $normalizedAnswer = WordBuildHelper::normalizeWord($answer);
        $normalizedLetter = WordBuildHelper::normalizeLetter($letter);

        if ($normalizedAnswer === '' || $normalizedLetter === '') {
            return false;
        }

        $first = mb_substr($normalizedAnswer, 0, 1);

        return WordBuildHelper::normalizeLetter($first) === $normalizedLetter;
    }

    /**
     * @param  array<int, array{letter?: string, question_text?: string, answer_text?: string}>  $cells
     * @return array<int, string>
     */
    public static function validateCells(array $cells): array
    {
        $errors = [];
        $seenLetters = [];

        foreach ($cells as $index => $cell) {
            $letter = WordBuildHelper::normalizeLetter(trim((string) ($cell['letter'] ?? '')));
            $question = trim((string) ($cell['question_text'] ?? ''));
            $answer = trim((string) ($cell['answer_text'] ?? ''));

            if ($letter === '') {
                $errors[] = 'الحرف في الخلية '.($index + 1).' مطلوب.';

                continue;
            }

            if (isset($seenLetters[$letter])) {
                $errors[] = 'الحرف «'.$letter.'» مكرر.';

                continue;
            }
            $seenLetters[$letter] = true;

            if ($question === '') {
                $errors[] = 'نص السؤال للحرف «'.$letter.'» مطلوب.';
            }

            if ($answer === '') {
                $errors[] = 'الإجابة للحرف «'.$letter.'» مطلوبة.';
            } elseif (! self::answerStartsWithLetter($answer, $letter)) {
                $errors[] = 'إجابة الحرف «'.$letter.'» يجب أن تبدأ بنفس الحرف.';
            }
        }

        return $errors;
    }

    /**
     * @param  array<int, array{row?: int, col?: int}>  $existing
     * @return array{row: int, col: int}
     */
    public static function nextPosition(array $existing): array
    {
        $index = count($existing);
        $preset = config('letter_grid.default_layout', []);

        if (isset($preset[$index])) {
            return [
                'row' => (int) $preset[$index]['row'],
                'col' => (int) $preset[$index]['col'],
            ];
        }

        $overflow = $index - count($preset);
        $row = (int) (floor(count($preset) / 5) + floor($overflow / 5));
        $col = $overflow % 5;

        return ['row' => max($row, 0), 'col' => $col];
    }

    /**
     * @return array<int, array{letter: string, row: int, col: int, question_text: string, answer_text: string}>
     */
    public static function emptyStarterCells(int $count = 8): array
    {
        $cells = [];
        for ($i = 0; $i < $count; $i++) {
            $pos = self::nextPosition($cells);
            $cells[] = [
                'letter' => '',
                'row' => $pos['row'],
                'col' => $pos['col'],
                'question_text' => '',
                'answer_text' => '',
            ];
        }

        return $cells;
    }

    /**
     * @return array<int, array{letter: string, row: int, col: int}>
     */
    public static function defaultCells(): array
    {
        $layout = config('letter_grid.default_layout', []);
        $letters = config('letter_grid.default_letters', []);

        return collect($layout)
            ->values()
            ->map(function ($pos, $index) use ($letters) {
                return [
                    'letter' => $letters[$index] ?? '',
                    'row' => (int) ($pos['row'] ?? 0),
                    'col' => (int) ($pos['col'] ?? 0),
                    'question_text' => '',
                    'answer_text' => '',
                ];
            })
            ->filter(fn ($cell) => filled($cell['letter']))
            ->values()
            ->all();
    }
}
