<?php

namespace App\Services\Game;

use App\Models\LetterGridGame;
use App\Models\Team;

class LetterGridWinnerCalculator
{
    public static function determine(LetterGridGame $game): ?Team
    {
        $teams = $game->relationLoaded('teams')
            ? $game->teams->sortBy('id')->values()
            : $game->teams()->orderBy('id')->get();

        if ($teams->count() < 2) {
            return $teams->first();
        }

        $sorted = $teams->sortByDesc('score')->values();
        $top = $sorted->first();
        $second = $sorted->get(1);

        if (! $top || ! $second) {
            return $top;
        }

        if ((int) $top->score === (int) $second->score) {
            return null;
        }

        return $top;
    }
}
