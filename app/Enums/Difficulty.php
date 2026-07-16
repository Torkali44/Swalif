<?php

namespace App\Enums;

enum Difficulty: string
{
    case Easy = 'easy';
    case Medium = 'medium';
    case Hard = 'hard';

    public function points(): int
    {
        return match ($this) {
            self::Easy => 200,
            self::Medium => 400,
            self::Hard => 600,
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Easy => 'سهل',
            self::Medium => 'متوسط',
            self::Hard => 'صعب',
        };
    }
}
