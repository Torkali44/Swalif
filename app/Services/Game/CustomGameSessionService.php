<?php

namespace App\Services\Game;

use App\Enums\GameStatus;
use App\Models\Category;
use App\Models\CustomGame;
use App\Models\User;
use App\Services\Category\QuestionPickerService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CustomGameSessionService
{
    public function __construct(private QuestionPickerService $picker) {}

    /**
     * إنشاء لعبة خاصة جديدة مع فرق وقفل أسئلة كل فئة.
     *
     * @param User  $user
     * @param array $data  [name, team_one, team_two, category_ids[]]
     */
    public function start(User $user, array $data): CustomGame
    {
        return DB::transaction(function () use ($user, $data) {
            // إنشاء اللعبة
            $game = CustomGame::create([
                'user_id'    => $user->id,
                'name'       => $data['name'],
                'status'     => GameStatus::Playing,
                'started_at' => now(),
            ]);

            // إنشاء الفرق
            foreach ([$data['team_one'], $data['team_two']] as $teamName) {
                $game->teams()->create([
                    'custom_game_id' => $game->id,
                    'game_id'        => null,
                    'name'           => $teamName,
                    'score'          => 0,
                    'helpers_left'   => config('game.default_helpers'),
                ]);
            }

            // ربط الفئات بترتيب محدد وقفل الأسئلة
            $categoryIds = (array) $data['category_ids'];
            $categories  = Category::whereIn('id', $categoryIds)
                ->where('is_active', true)
                ->get()
                ->keyBy('id');

            foreach ($categoryIds as $order => $catId) {
                $category = $categories->get($catId);
                if (! $category) {
                    continue;
                }

                // ربط الفئة بالترتيب
                $game->categories()->attach($catId, ['sort_order' => $order]);

                // جلب أسئلة هذه الفئة بنفس QuestionPickerService الحالي
                $questions = $this->picker->forBoard($category);

                foreach ($questions->unique('id') as $question) {
                    $game->customGameQuestions()->firstOrCreate([
                        'question_id' => $question->id,
                    ], [
                        'category_id' => $catId,
                    ]);
                }
            }

            return $game;
        });
    }

    /**
     * التحقق من ملكية اللعبة.
     */
    public function ensureOwned(CustomGame $game, User $user): void
    {
        abort_unless($game->user_id === $user->id || $user->is_admin, 403);
    }

    /**
     * بناء بيانات الـ Board لفئة محددة داخل اللعبة الخاصة.
     * يُعيد نفس البنية التي يستخدمها GameController::board().
     */
    public function buildBoardForCategory(CustomGame $game, Category $category): array
    {
        $perLevel = (int) config('game.questions_per_level', 6);

        $cgqsForCategory = $game->customGameQuestions()
            ->where('category_id', $category->id)
            ->with('question')
            ->get()
            ->filter(fn ($cgq) => $cgq->question);

        // Pre-compute answered question IDs to avoid N+1 queries inside $mapCell
        $answeredQuestionIds = $cgqsForCategory
            ->filter(fn ($cgq) => $cgq->answered_at !== null)
            ->pluck('question_id')
            ->flip();

        $boardQuestions = $cgqsForCategory
            ->map(fn ($cgq) => $cgq->question)
            ->values();

        $placedIds = collect();
        $mapCell   = fn ($question) => $question ? [
            'question' => $question,
            'points'   => ($question->level instanceof \BackedEnum
                ? $question->level
                : \App\Enums\Difficulty::tryFrom((string) $question->level))?->points()
                ?? (int) $question->points,
            'used' => isset($answeredQuestionIds[$question->id]),
        ] : null;

        $byLevel = function (string $level, int $points) use ($boardQuestions, $mapCell, $perLevel, &$placedIds) {
            $items = $boardQuestions
                ->filter(function ($q) use ($level, $placedIds) {
                    if ($placedIds->contains($q->id)) {
                        return false;
                    }
                    $qLevel = $q->level instanceof \BackedEnum ? $q->level->value : (string) $q->level;
                    return $qLevel === $level;
                })
                ->unique('id')
                ->take($perLevel)
                ->values();

            if ($items->count() < $perLevel) {
                $fill = $boardQuestions
                    ->filter(function ($q) use ($points, $placedIds, $items) {
                        if ($placedIds->contains($q->id) || $items->contains(fn ($i) => (int) $i->id === (int) $q->id)) {
                            return false;
                        }
                        return (int) $q->points === $points;
                    })
                    ->unique('id')
                    ->take($perLevel - $items->count())
                    ->values();
                $items = $items->concat($fill)->unique('id')->take($perLevel)->values();
            }

            $placedIds = $placedIds->merge($items->pluck('id'))->unique()->values();

            return $items->map($mapCell)->pad($perLevel, null);
        };

        return [
            'easy'   => $byLevel('easy', 200),
            'medium' => $byLevel('medium', 400),
            'hard'   => $byLevel('hard', 600),
        ];
    }

    /**
     * حساب الفريق النشط بناءً على عدد الأسئلة التي تم الإجابة عليها.
     */
    public function activeTeam(CustomGame $game): ?\App\Models\Team
    {
        $answered = $game->customGameQuestions->whereNotNull('answered_at')->count();
        $teams    = $game->teams->values();

        return $teams->count() > 0
            ? (($answered % 2 === 0) ? $teams->get(0) : $teams->get(1))
            : null;
    }
}
