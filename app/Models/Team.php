<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    protected $fillable = [
        'game_id',
        'custom_game_id',
        'letter_grid_game_id',
        'character_id',
        'name',
        'score',
        'helpers_left',
    ];

    protected $casts = ['helpers_left' => 'array'];

    public function game()
    {
        return $this->belongsTo(Game::class);
    }

    public function customGame()
    {
        return $this->belongsTo(CustomGame::class);
    }

    public function letterGridGame()
    {
        return $this->belongsTo(LetterGridGame::class);
    }

    public function character()
    {
        return $this->belongsTo(Character::class);
    }
}
