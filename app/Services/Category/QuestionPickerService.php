<?php

namespace App\Services\Category;

use App\Enums\Difficulty;
use App\Models\Category;
use App\Models\User;
use Illuminate\Support\Collection;

class QuestionPickerService
{
    public function __construct(private CategoryPlayPoolService $pool) {}

    /**
     * Pick a play board: 2 questions per difficulty by default (easy / medium / hard).
     * For authenticated users, skips questions they already consumed in this category.
     */
    public function forBoard(Category $category, ?int $perLevel = null, ?User $user = null): Collection
    {
        $perLevel ??= $this->pool->boardPerLevel();
        $excludeIds = collect();

        if ($user) {
            $this->pool->resetIfExhausted($user, $category);
            $excludeIds = $this->pool->playedQuestionIds($user, (int) $category->id);
        }

        $picked = collect();
        $pickedIds = collect();

        foreach (Difficulty::cases() as $level) {
            $query = $category->questions()
                ->where('is_active', true)
                ->where('level', $level->value)
                ->whereNotIn('id', $pickedIds->merge($excludeIds));

            $questions = (clone $query)
                ->inRandomOrder()
                ->limit($perLevel)
                ->get();

            // Fallback by points if level column is inconsistent
            if ($questions->count() < $perLevel) {
                $byPoints = $category->questions()
                    ->where('is_active', true)
                    ->where('points', $level->points())
                    ->whereNotIn('id', $questions->pluck('id')->merge($pickedIds)->merge($excludeIds))
                    ->inRandomOrder()
                    ->limit($perLevel - $questions->count())
                    ->get();

                $questions = $questions->concat($byPoints);
            }

            $batch = $questions->unique('id')->take($perLevel)->values();
            $picked = $picked->concat($batch);
            $pickedIds = $picked->pluck('id');
        }

        return $picked->unique('id')->values();
    }
}
