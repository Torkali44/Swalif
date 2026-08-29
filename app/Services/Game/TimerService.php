<?php

namespace App\Services\Game;

use App\Models\Question;

class TimerService
{
    public function limitFor(Question $question): int
    {
        if ($question->time_limit) {
            return (int) $question->time_limit;
        }

        return match ($question->type) {
            'word_build' => (int) config('game.word_build_time_limit', 15),
            'audio' => (int) config('game.audio_time_limit', 60),
            default => (int) config('game.default_time_limit', 30),
        };
    }
}
