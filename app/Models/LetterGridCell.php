<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LetterGridCell extends Model
{
    protected $fillable = [
        'letter_grid_id',
        'letter',
        'row',
        'col',
        'question_text',
        'answer_text',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function grid()
    {
        return $this->belongsTo(LetterGrid::class, 'letter_grid_id');
    }
}
