<?php

namespace App\Services\Game;

use App\Models\Game;
use App\Models\GameQuestion;
use App\Models\Team;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ScoringService
{
    /**
     * @return bool true when points were assigned, false when already scored
     */
    public function assignPoints(Game $game, GameQuestion $gameQuestion, ?Team $team): bool
    {
        return DB::transaction(function () use ($game, $gameQuestion, $team) {
            $gameQuestion = GameQuestion::query()
                ->whereKey($gameQuestion->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($gameQuestion->answered_at !== null) {
                return false;
            }

            if ($team && (int) $team->game_id !== (int) $game->id) {
                throw new RuntimeException('الفريق غير مرتبط بهذه اللعبة.');
            }

            $game->loadMissing('teams');
            $teams = $game->teams->values();
            $answeredBefore = GameQuestion::query()
                ->where('game_id', $game->id)
                ->whereNotNull('answered_at')
                ->count();

            $turnTeam = $teams->count() > 0
                ? $teams->get($answeredBefore % $teams->count())
                : null;

            $gameQuestion->loadMissing('question');
            $points = $team ? $gameQuestion->question->displayPoints() : 0;

            $gameQuestion->update([
                'turn_team_id' => $turnTeam?->id,
                'assigned_team_id' => $team?->id,
                'points_awarded' => $points,
                'answered_correctly' => (bool) $team,
                'answered_at' => now(),
            ]);

            if ($team && $points > 0) {
                Team::query()->whereKey($team->id)->increment('score', $points);
            }

            return true;
        });
    }
}
