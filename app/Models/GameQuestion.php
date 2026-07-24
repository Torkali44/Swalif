<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GameQuestion extends Model
{
    protected $fillable = [
        'game_id',
        'question_id',
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
        'answered_at' => 'datetime',
    ];

    public function game()
    {
        return $this->belongsTo(Game::class);
    }

    public function question()
    {
        return $this->belongsTo(Question::class);
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

    public function playerChoseCorrectly(): ?bool
    {
        if ($this->selected_option_id) {
            $this->loadMissing('selectedOption');

            return (bool) $this->selectedOption?->is_correct;
        }

        return null;
    }
}
