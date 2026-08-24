<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Support\PublicMedia;

class Classification extends Model
{
    protected $fillable = [
        'name_ar',
        'name_en',
        'slug',
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

    public function categories()
    {
        return $this->hasMany(Category::class);
    }

    public function imageUrl(): ?string
    {
        return PublicMedia::url($this->image);
    }
}
