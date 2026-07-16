<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Category extends Model
{
    protected $fillable = [
        'name_ar',
        'name_en',
        'slug',
        'group',
        'icon',
        'image',
        'description',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function questions()
    {
        return $this->hasMany(Question::class);
    }

    public function games()
    {
        return $this->hasMany(Game::class);
    }

    public function imageUrl(): ?string
    {
        if (! $this->image) {
            return null;
        }

        if (Storage::disk('public')->exists($this->image)) {
            return '/storage/'.ltrim($this->image, '/');
        }

        return null;
    }
}
