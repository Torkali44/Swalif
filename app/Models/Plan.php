<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    protected $fillable = [
        'name',
        'icon',
        'type',
        'price',
        'old_price',
        'currency',
        'duration_days',
        'features',
        'is_active',
        'is_recommended',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'features' => 'array',
            'is_active' => 'boolean',
            'is_recommended' => 'boolean',
            'price' => 'decimal:2',
            'old_price' => 'decimal:2',
        ];
    }


    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }
}
