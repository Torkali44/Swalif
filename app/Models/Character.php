<?php

namespace App\Models;

use App\Support\PublicMedia;
use Illuminate\Database\Eloquent\Model;

class Character extends Model
{
    protected $fillable = [
        'name_ar',
        'name_en',
        'slug',
        'image',
        'icon',
        'accent_color',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function imageUrl(): ?string
    {
        return PublicMedia::url($this->image);
    }

    public function accentGradient(): string
    {
        $c1 = $this->accent_color ?: '#1E3A5F';
        $c2 = $this->accentColorDark();

        return "linear-gradient(135deg,{$c1},{$c2})";
    }

    private function accentColorDark(): string
    {
        $hex = ltrim((string) ($this->accent_color ?: '#1E3A5F'), '#');
        if (strlen($hex) !== 6) {
            return '#0F2440';
        }

        $r = max(0, hexdec(substr($hex, 0, 2)) - 40);
        $g = max(0, hexdec(substr($hex, 2, 2)) - 40);
        $b = max(0, hexdec(substr($hex, 4, 2)) - 40);

        return sprintf('#%02X%02X%02X', $r, $g, $b);
    }
}
