<?php

namespace App\Services\Game;

use App\Enums\GameStatus;
use App\Models\Category;
use App\Models\CustomGame;
use App\Models\LetterGrid;
use App\Models\LetterGridGame;
use App\Models\User;
use App\Services\Category\CategoryPlayPoolService;
use App\Services\Category\QuestionPickerService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CustomGameSessionService
{
    public function __construct(
        private QuestionPickerService $picker,
        private CategoryPlayPoolService $pool,
    ) {}

    /**
     * إنشاء لعبة خاصة جديدة مع فرق وقفل أسئلة كل فئة + شبكات الحروف.
     *
     * @param  array  $data  [name, team_one, team_two, category_ids[], letter_grid_ids[]]
     */
    public function start(User $user, array $data): CustomGame
    {
        return DB::transaction(function () use ($user, $data) {
            $game = CustomGame::create([
                'user_id' => $user->id,
                'name' => $data['name'],
                'status' => GameStatus::Playing,
                'started_at' => now(),
            ]);

            foreach ([
                ['name' => $data['team_one'], 'character_id' => $data['team_one_character_id'] ?? null],
                ['name' => $data['team_two'], 'character_id' => $data['team_two_character_id'] ?? null],
            ] as $teamData) {
                $game->teams()->create([
                    'custom_game_id' => $game->id,
                    'game_id' => null,
                    'name' => $teamData['name'],
                    'character_id' => $teamData['character_id'],
                    'score' => 0,
                    'helpers_left' => config('game.default_helpers'),
                ]);
            }

            $categoryIds = array_values(array_map('intval', (array) ($data['category_ids'] ?? [])));
            $letterGridIds = array_values(array_map('intval', (array) ($data['letter_grid_ids'] ?? [])));

            $categories = Category::whereIn('id', $categoryIds)
                ->where('is_active', true)
                ->get()
                ->keyBy('id');

            $lockedAny = false;
            $sortOrder = 0;

            foreach ($categoryIds as $catId) {
                $category = $categories->get($catId);
                if (! $category) {
                    continue;
                }

                $game->categories()->attach($catId, ['sort_order' => $sortOrder++]);

                $questions = $this->picker->forBoard($category, null, $user);

                foreach ($questions->unique('id') as $question) {
                    $game->customGameQuestions()->firstOrCreate([
                        'question_id' => $question->id,
                    ], [
                        'category_id' => $catId,
                    ]);
                }

                if ($questions->isNotEmpty()) {
                    $lockedAny = true;
                    $this->pool->recordPlays(
                        $user,
                        $category,
                        $questions,
                        'custom_game',
                        null,
                        $game->id,
                    );
                }
            }

            $grids = LetterGrid::query()
                ->whereIn('id', $letterGridIds)
                ->where('is_active', true)
                ->withCount(['cells as playable_cells_count' => fn ($q) => $q->where('is_active', true)])
                ->get()
                ->keyBy('id');

            $attachedGrids = 0;
            foreach ($letterGridIds as $gridId) {
                $grid = $grids->get($gridId);
                if (! $grid || (int) $grid->playable_cells_count <= 0) {
                    continue;
                }
                $game->letterGrids()->attach($gridId, ['sort_order' => $sortOrder++]);
                $attachedGrids++;
            }

            if (! $lockedAny && $attachedGrids === 0) {
                throw ValidationException::withMessages([
                    'category_ids' => 'لا توجد أسئلة أو شبكات حروف كافية للعب الآن.',
                ]);
            }

            return $game;
        });
    }

    public function ensureOwned(CustomGame $game, User $user): void
    {
        abort_unless($game->user_id === $user->id || $user->is_admin, 403);
    }

    /**
     * بدء أو استئناف جلسة شبكة حروف من داخل لعبة خاصة.
     */
    public function startOrResumeLetterGrid(CustomGame $customGame, LetterGrid $grid, User $user): LetterGridGame
    {
        $this->ensureOwned($customGame, $user);

        abort_unless(
            $customGame->letterGrids()->where('letter_grids.id', $grid->id)->exists(),
            404
        );

        $existing = LetterGridGame::query()
            ->where('custom_game_id', $customGame->id)
            ->where('letter_grid_id', $grid->id)
            ->latest('id')
            ->first();

        if ($existing && $existing->isFinished()) {
            throw ValidationException::withMessages([
                'letter_grid' => app(LetterGridSessionService::class)->finishedReplayMessage($existing->loadMissing('grid')),
            ]);
        }

        if ($existing && $existing->isPlaying()) {
            return $existing;
        }

        $teams = $customGame->teams()->with('character')->orderBy('id')->get();
        abort_unless($teams->count() >= 2, 422, 'يجب وجود فريقين للعب.');

        return app(LetterGridSessionService::class)->start($user, [
            'letter_grid_id' => $grid->id,
            'name' => $customGame->name.' — '.$grid->name_ar,
            'team_one' => $teams->get(0)->name,
            'team_two' => $teams->get(1)->name,
            'team_one_character_id' => $teams->get(0)->character_id,
            'team_two_character_id' => $teams->get(1)->character_id,
            'custom_game_id' => $customGame->id,
        ]);
    }

    /**
     * بناء بيانات الـ Board لفئة محددة داخل اللعبة الخاصة.
     */
    public function buildBoardForCategory(CustomGame $game, Category $category): array
    {
        $perLevel = max(1, (int) config('game.board_questions_per_level', 2));

        $cgqsForCategory = $game->customGameQuestions()
            ->where('category_id', $category->id)
            ->with('question')
            ->get()
            ->filter(fn ($cgq) => $cgq->question);

        $answeredQuestionIds = $cgqsForCategory
            ->filter(fn ($cgq) => $cgq->answered_at !== null)
            ->pluck('question_id')
            ->flip();

        $boardQuestions = $cgqsForCategory
            ->map(fn ($cgq) => $cgq->question)
            ->values();

        $placedIds = collect();
        $mapCell = fn ($question) => $question ? [
            'question' => $question,
            'points' => ($question->level instanceof \BackedEnum
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
            'easy' => $byLevel('easy', 200),
            'medium' => $byLevel('medium', 400),
            'hard' => $byLevel('hard', 600),
        ];
    }

    public function activeTeam(CustomGame $game): ?\App\Models\Team
    {
        $answered = $game->customGameQuestions->whereNotNull('answered_at')->count();
        $teams = $game->teams->values();

        return $teams->count() > 0
            ? (($answered % 2 === 0) ? $teams->get(0) : $teams->get(1))
            : null;
    }
}
