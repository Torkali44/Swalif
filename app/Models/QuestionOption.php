<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class QuestionOption extends Model { public $timestamps=false; protected $fillable=['option_text','is_correct']; protected $casts=['is_correct'=>'boolean']; public function question(){return $this->belongsTo(Question::class);} }
