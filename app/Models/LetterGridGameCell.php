<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LetterGridGameCell extends Model
{
    protected $fillable = [
        'letter_grid_game_id',
        'letter',
        'row',
        'col',
        'question_text',
        'answer_text',
        'claimed_team_id',
        'turn_team_id',
        'answered_correctly',
        'answered_at',
    ];

    protected function casts(): array
    {
        return [
            'answered_correctly' => 'boolean',
            'answered_at' => 'datetime',
        ];
    }

    public function game()
    {
        return $this->belongsTo(LetterGridGame::class, 'letter_grid_game_id');
    }

    public function claimedTeam()
    {
        return $this->belongsTo(Team::class, 'claimed_team_id');
    }

    public function turnTeam()
    {
        return $this->belongsTo(Team::class, 'turn_team_id');
    }

    public function isClaimed(): bool
    {
        return filled($this->claimed_team_id);
    }

    public function isResolved(): bool
    {
        return filled($this->answered_at);
    }

    public function isMissed(): bool
    {
        return $this->isResolved() && ! $this->isClaimed();
    }
}
