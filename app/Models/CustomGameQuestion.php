<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomGameQuestion extends Model
{
    protected $fillable = [
        'custom_game_id',
        'question_id',
        'category_id',
        'selected_option_id',
        'player_answer',
        'turn_team_id',
        'assigned_team_id',
        'points_awarded',
        'answered_correctly',
        'answered_at',
    ];

    protected $casts = [
        'answered_correctly' => 'boolean',
        'answered_at'        => 'datetime',
    ];

    // ── Relations ──────────────────────────────────────────────

    public function customGame()
    {
        return $this->belongsTo(CustomGame::class);
    }

    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function team()
    {
        return $this->belongsTo(Team::class, 'assigned_team_id');
    }

    public function turnTeam()
    {
        return $this->belongsTo(Team::class, 'turn_team_id');
    }

    public function selectedOption()
    {
        return $this->belongsTo(QuestionOption::class, 'selected_option_id');
    }

    // ── Helpers ─────────────────────────────────────────────────

    public function playerChoseCorrectly(): ?bool
    {
        if ($this->selected_option_id) {
            $this->loadMissing('selectedOption');

            return (bool) $this->selectedOption?->is_correct;
        }

        return null;
    }
}
