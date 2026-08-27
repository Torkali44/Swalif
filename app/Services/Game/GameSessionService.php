<?php

namespace App\Services\Game;

use App\Enums\GameStatus;
use App\Models\Game;
use App\Models\User;
use App\Services\Category\CategoryPlayPoolService;
use App\Services\Category\QuestionPickerService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class GameSessionService
{
    public function __construct(
        private QuestionPickerService $picker,
        private CategoryPlayPoolService $pool,
    ) {}

    public function start(User $user, array $data): Game
    {
        return DB::transaction(function () use ($user, $data) {
            $game = Game::create([
                'user_id' => $user->id,
                'category_id' => $data['category_id'],
                'name' => $data['name'],
                'status' => GameStatus::Playing->value,
                'started_at' => now(),
            ]);

            foreach ([
                ['name' => $data['team_one'], 'character_id' => $data['team_one_character_id'] ?? null],
                ['name' => $data['team_two'], 'character_id' => $data['team_two_character_id'] ?? null],
            ] as $teamData) {
                $game->teams()->create([
                    'name' => $teamData['name'],
                    'character_id' => $teamData['character_id'],
                    'score' => 0,
                    'helpers_left' => config('game.default_helpers'),
                ]);
            }

            $game->load('category');
            $questions = $this->picker->forBoard($game->category, null, $user);

            if ($questions->isEmpty()) {
                throw ValidationException::withMessages([
                    'category_id' => 'لا توجد أسئلة كافية في هذه الفئة للعب الآن.',
                ]);
            }

            foreach ($questions->unique('id') as $question) {
                $game->gameQuestions()->firstOrCreate([
                    'question_id' => $question->id,
                ]);
            }

            $this->pool->recordPlays(
                $user,
                $game->category,
                $questions,
                'game',
                $game->id,
            );

            return $game;
        });
    }

    public function ensureOwned(Game $game, User $user): void
    {
        abort_unless($game->user_id === $user->id || $user->is_admin, 403);
    }
}
