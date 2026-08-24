<?php

namespace App\Models;

use App\Enums\GameStatus;
use Illuminate\Database\Eloquent\Model;

class CustomGame extends Model
{
    protected $fillable = [
        'user_id',
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
            'ended_at'   => 'datetime',
            'status'     => GameStatus::class,
        ];
    }

    // ── Relations ──────────────────────────────────────────────

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /** الفئات المختارة لهذه اللعبة (4-6 فئات) */
    public function categories()
    {
        return $this->belongsToMany(
            Category::class,
            'custom_game_categories',
            'custom_game_id',
            'category_id'
        )->withPivot('sort_order')->orderByPivot('sort_order');
    }

    /** الفرق — تستخدم custom_game_id في جدول teams */
    public function teams()
    {
        return $this->hasMany(Team::class, 'custom_game_id')
                    ->orderBy('id');
    }

    /** أسئلة اللعبة الخاصة */
    public function customGameQuestions()
    {
        return $this->hasMany(CustomGameQuestion::class);
    }

    public function winner()
    {
        return $this->belongsTo(Team::class, 'winner_team_id');
    }

    // ── Helpers ─────────────────────────────────────────────────

    public function isFinished(): bool
    {
        return $this->status === GameStatus::Finished;
    }
}
