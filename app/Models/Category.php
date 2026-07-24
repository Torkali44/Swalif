<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Support\PublicMedia;

class Category extends Model
{
    protected $fillable = [
        'name_ar',
        'name_en',
        'slug',
        'group',
        'classification_id',
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

    public function classification()
    {
        return $this->belongsTo(Classification::class);
    }

    public function games()
    {
        return $this->hasMany(Game::class);
    }

    public function classificationName(): string
    {
        return $this->classification?->name_ar ?: ($this->group ?: 'بدون تصنيف');
    }

    public function imageUrl(): ?string
    {
        return PublicMedia::url($this->image);
    }
}
