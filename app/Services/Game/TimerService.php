<?php

namespace App\Services\Game;

use App\Models\Question;

class TimerService
{
    public function limitFor(Question $question): int
    {
        return (int) ($question->time_limit ?: config('game.default_time_limit', 60));
    }
}
