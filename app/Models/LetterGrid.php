<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class LetterGrid extends Model
{
    protected $fillable = [
        'name_ar',
        'slug',
        'description',
        'image',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (LetterGrid $grid) {
            if (blank($grid->slug) && filled($grid->name_ar)) {
                $grid->slug = Str::slug($grid->name_ar) ?: 'grid-'.Str::random(6);
            }
        });
    }

    public function cells()
    {
        return $this->hasMany(LetterGridCell::class)->orderBy('row')->orderBy('col');
    }

    public function activeCells()
    {
        return $this->cells()->where('is_active', true);
    }

    public function games()
    {
        return $this->hasMany(LetterGridGame::class);
    }

    public function imageUrl(): ?string
    {
        return \App\Support\PublicMedia::url($this->image);
    }
}
