<?php

namespace App\Services\Category;

use App\Enums\Difficulty;
use App\Models\Category;
use App\Models\Question;
use App\Models\User;
use App\Models\UserCategoryQuestionPlay;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class CategoryPlayPoolService
{
    private function poolTableReady(): bool
    {
        static $ready = null;

        if ($ready === null) {
            $ready = Schema::hasTable('user_category_question_plays');
        }

        return $ready;
    }

    private function fallbackDecorate(Collection $categories): Collection
    {
        return $categories->each(function (Category $category) {
            $total = (int) ($category->questions_count ?? 0);
            $category->setAttribute('remaining_questions', $total);
            $category->setAttribute('remaining_rounds', null);
            $category->setAttribute(
                'remaining_badge',
                $total > 0 ? "{$total} سؤال" : 'قريبًا'
            );
        });
    }

    public function boardPerLevel(): int
    {
        return max(1, (int) config('game.board_questions_per_level', 2));
    }

    public function questionsPerRound(): int
    {
        return $this->boardPerLevel() * count(Difficulty::cases());
    }

    public function playedQuestionIds(User $user, int $categoryId): Collection
    {
        if (! $this->poolTableReady()) {
            return collect();
        }

        return UserCategoryQuestionPlay::query()
            ->where('user_id', $user->id)
            ->where('category_id', $categoryId)
            ->pluck('question_id');
    }

    public function remainingQuestionsCount(User $user, int $categoryId): int
    {
        $playedIds = $this->playedQuestionIds($user, $categoryId);

        return Question::query()
            ->where('category_id', $categoryId)
            ->where('is_active', true)
            ->when($playedIds->isNotEmpty(), fn ($q) => $q->whereNotIn('id', $playedIds))
            ->count();
    }

    /**
     * How many full 2+2+2 rounds the user can still play in this category.
     */
    public function remainingRoundsCount(User $user, int $categoryId): int
    {
        $perLevel = $this->boardPerLevel();
        $playedIds = $this->playedQuestionIds($user, $categoryId);

        $unusedByLevel = Question::query()
            ->where('category_id', $categoryId)
            ->where('is_active', true)
            ->when($playedIds->isNotEmpty(), fn ($q) => $q->whereNotIn('id', $playedIds))
            ->get(['id', 'level'])
            ->groupBy(fn ($q) => $q->level instanceof \BackedEnum ? $q->level->value : (string) $q->level);

        $rounds = null;
        foreach (Difficulty::cases() as $level) {
            $count = $unusedByLevel->get($level->value, collect())->count();
            $levelRounds = intdiv($count, $perLevel);
            $rounds = $rounds === null ? $levelRounds : min($rounds, $levelRounds);
        }

        return max(0, (int) ($rounds ?? 0));
    }

    public function canStartRound(User $user, Category $category): bool
    {
        // Need at least one unused active question to start something.
        return $this->remainingQuestionsCount($user, (int) $category->id) > 0;
    }

    /**
     * When the user has consumed every active question in the category,
     * clear history so the next round can start fresh.
     */
    public function resetIfExhausted(User $user, Category $category): bool
    {
        $remaining = $this->remainingQuestionsCount($user, (int) $category->id);
        if ($remaining > 0) {
            return false;
        }

        $total = Question::query()
            ->where('category_id', $category->id)
            ->where('is_active', true)
            ->count();

        if ($total === 0 || ! $this->poolTableReady()) {
            return false;
        }

        UserCategoryQuestionPlay::query()
            ->where('user_id', $user->id)
            ->where('category_id', $category->id)
            ->delete();

        return true;
    }

    public function recordPlays(
        User $user,
        Category $category,
        Collection $questions,
        string $source = 'game',
        ?int $gameId = null,
        ?int $customGameId = null,
    ): void {
        if (! $this->poolTableReady()) {
            return;
        }

        $now = now();
        $rows = $questions->unique('id')->map(fn ($question) => [
            'user_id' => $user->id,
            'question_id' => $question->id,
            'category_id' => $category->id,
            'source' => $source,
            'game_id' => $gameId,
            'custom_game_id' => $customGameId,
            'played_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ])->values()->all();

        if ($rows === []) {
            return;
        }

        UserCategoryQuestionPlay::query()->upsert(
            $rows,
            ['user_id', 'question_id'],
            ['updated_at']
        );
    }

    /**
     * Attach remaining_questions / badge fields onto category models for views.
     */
    public function decorateCategories(Collection $categories, ?User $user): Collection
    {
        if ($categories->isEmpty()) {
            return $categories;
        }

        if (! $user || ! $this->poolTableReady()) {
            return $this->fallbackDecorate($categories);
        }

        try {
            $categoryIds = $categories->pluck('id')->all();

            $playedByCategory = UserCategoryQuestionPlay::query()
                ->where('user_id', $user->id)
                ->whereIn('category_id', $categoryIds)
                ->get(['category_id', 'question_id'])
                ->groupBy('category_id')
                ->map(fn ($rows) => $rows->pluck('question_id'));

            $activeByCategory = Question::query()
                ->whereIn('category_id', $categoryIds)
                ->where('is_active', true)
                ->get(['id', 'category_id'])
                ->groupBy('category_id');

            return $categories->each(function (Category $category) use ($playedByCategory, $activeByCategory) {
                $activeIds = $activeByCategory->get($category->id, collect())->pluck('id');
                $total = $activeIds->count();
                $playedIds = $playedByCategory->get($category->id, collect());
                $remaining = $activeIds->diff($playedIds)->count();

                $category->setAttribute('remaining_questions', $remaining);
                $category->setAttribute(
                    'remaining_badge',
                    $remaining > 0
                        ? "{$remaining} سؤال"
                        : ($total > 0 ? 'مكتملة' : 'قريبًا')
                );
            });
        } catch (\Throwable) {
            return $this->fallbackDecorate($categories);
        }
    }
}
