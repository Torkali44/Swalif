<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class GameQuestion extends Model { protected $fillable=['game_id','question_id','assigned_team_id','points_awarded','answered_correctly','answered_at']; protected $casts=['answered_correctly'=>'boolean','answered_at'=>'datetime']; public function game(){return $this->belongsTo(Game::class);} public function question(){return $this->belongsTo(Question::class);} public function team(){return $this->belongsTo(Team::class,'assigned_team_id');} }
