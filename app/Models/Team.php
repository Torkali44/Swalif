<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Team extends Model { protected $fillable=['game_id','name','score','helpers_left']; protected $casts=['helpers_left'=>'array']; public function game(){return $this->belongsTo(Game::class);} }
