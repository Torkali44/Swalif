<?php

namespace App\Services\Game;

use App\Enums\GameStatus;
use App\Models\Game;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class GameSessionService
{
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

            foreach ([$data['team_one'], $data['team_two']] as $name) {
                $game->teams()->create([
                    'name' => $name,
                    'score' => 0,
                    'helpers_left' => config('game.default_helpers'),
                ]);
            }

            return $game;
        });
    }

    public function ensureOwned(Game $game, User $user): void
    {
        abort_unless($game->user_id === $user->id || $user->is_admin, 403);
    }
}
