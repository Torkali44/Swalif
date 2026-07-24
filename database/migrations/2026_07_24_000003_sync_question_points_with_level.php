<?php

use App\Enums\Difficulty;
use App\Models\Question;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        foreach (Difficulty::cases() as $level) {
            Question::query()
                ->where('level', $level->value)
                ->where('points', '!=', $level->points())
                ->update(['points' => $level->points()]);
        }
    }

    public function down(): void
    {
        //
    }
};
