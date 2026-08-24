<?php

namespace App\Services\Game;

use App\Models\Game;
use App\Models\Team;

class WinnerCalculator
{
    public function determine(Game $game): ?Team
    {
        // reorder() يشيل ترتيب الـ id الافتراضي عشان الفائز يبقى أعلى نقاط
        $teams = $game->teams()
            ->reorder()
            ->orderByDesc('score')
            ->orderBy('id')
            ->get();

        if ($teams->isEmpty()) {
            return null;
        }

        $top = $teams->first();
        $topScore = (int) $top->score;
        $tie = $teams->filter(fn (Team $team) => (int) $team->score === $topScore);

        return $tie->count() > 1 ? null : $top;
    }
}
