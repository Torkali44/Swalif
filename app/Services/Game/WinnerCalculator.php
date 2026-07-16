<?php

namespace App\Services\Game;

use App\Models\Game;
use App\Models\Team;

class WinnerCalculator
{
    public function determine(Game $game): ?Team
    {
        $teams = $game->teams()->orderByDesc('score')->get();

        if ($teams->isEmpty()) {
            return null;
        }

        $top = $teams->first();
        $tie = $teams->filter(fn (Team $team) => $team->score === $top->score);

        return $tie->count() > 1 ? null : $top;
    }
}
