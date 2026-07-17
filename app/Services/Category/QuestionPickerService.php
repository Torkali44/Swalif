<?php

namespace App\Services\Category;

use App\Enums\Difficulty;
use App\Models\Category;
use Illuminate\Support\Collection;

class QuestionPickerService
{
    /**
     * Pick a fixed board: 6 questions per difficulty (easy / medium / hard).
     */
    public function forBoard(Category $category, ?int $perLevel = null): Collection
    {
        $perLevel ??= (int) config('game.questions_per_level', 6);

        $picked = collect();

        foreach (Difficulty::cases() as $level) {
            $questions = $category->questions()
                ->where('is_active', true)
                ->where('level', $level->value)
                ->inRandomOrder()
                ->limit($perLevel)
                ->get();

            // Fallback by points if level column is inconsistent
            if ($questions->count() < $perLevel) {
                $byPoints = $category->questions()
                    ->where('is_active', true)
                    ->where('points', $level->points())
                    ->whereNotIn('id', $questions->pluck('id'))
                    ->inRandomOrder()
                    ->limit($perLevel - $questions->count())
                    ->get();

                $questions = $questions->concat($byPoints);
            }

            $picked = $picked->concat($questions->take($perLevel));
        }

        return $picked->values();
    }
}
