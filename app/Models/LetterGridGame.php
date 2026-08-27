<?php

namespace App\Models;

use App\Enums\GameStatus;
use Illuminate\Database\Eloquent\Model;

class LetterGridGame extends Model
{
    protected $fillable = [
        'user_id',
        'letter_grid_id',
        'custom_game_id',
        'name',
        'status',
        'winner_team_id',
        'active_cell_id',
        'turn_index',
        'answered_count',
        'custom_bonus_awarded',
        'started_at',
        'ended_at',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
            'custom_bonus_awarded' => 'boolean',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function grid()
    {
        return $this->belongsTo(LetterGrid::class, 'letter_grid_id');
    }

    public function customGame()
    {
        return $this->belongsTo(CustomGame::class);
    }

    public function teams()
    {
        return $this->hasMany(Team::class)->orderBy('id');
    }

    public function cells()
    {
        return $this->hasMany(LetterGridGameCell::class)->orderBy('row')->orderBy('col');
    }

    public function activeCell()
    {
        return $this->belongsTo(LetterGridGameCell::class, 'active_cell_id');
    }

    public function winnerTeam()
    {
        return $this->belongsTo(Team::class, 'winner_team_id');
    }

    public function isPlaying(): bool
    {
        return ($this->status ?? GameStatus::Playing->value) === GameStatus::Playing->value;
    }

    public function isFinished(): bool
    {
        return ($this->status ?? '') === GameStatus::Finished->value;
    }

    public function totalCells(): int
    {
        if ($this->relationLoaded('cells')) {
            return $this->cells->count();
        }

        return $this->cells()->count();
    }

    public function claimedCount(): int
    {
        if ($this->relationLoaded('cells')) {
            return $this->cells->filter(fn ($c) => filled($c->claimed_team_id))->count();
        }

        return $this->cells()->whereNotNull('claimed_team_id')->count();
    }

    public function resolvedCount(): int
    {
        if ($this->relationLoaded('cells')) {
            return $this->cells->filter(fn ($c) => filled($c->answered_at))->count();
        }

        return (int) ($this->answered_count ?? $this->cells()->whereNotNull('answered_at')->count());
    }

    public function isComplete(): bool
    {
        $total = $this->totalCells();

        return $total > 0 && $this->resolvedCount() >= $total;
    }

    public function currentTurnTeam(): ?Team
    {
        if (! $this->relationLoaded('teams')) {
            $this->load('teams');
        }

        $teams = $this->teams->values();

        if ($teams->isEmpty()) {
            return null;
        }

        return $teams->get($this->turn_index % $teams->count());
    }
}
