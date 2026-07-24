<?php

namespace App\Models;

use App\Enums\GameStatus;
use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    protected $fillable = [
        'user_id',
        'category_id',
        'name',
        'status',
        'winner_team_id',
        'started_at',
        'ended_at',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
            'status' => GameStatus::class,
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function teams()
    {
        return $this->hasMany(Team::class)->orderBy('id');
    }

    public function gameQuestions()
    {
        return $this->hasMany(GameQuestion::class);
    }

    public function winner()
    {
        return $this->belongsTo(Team::class, 'winner_team_id');
    }
}
