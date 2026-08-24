<?php

namespace App\Enums;

enum HelperType: string
{
    case Swap = 'swap';
    case PhoneFriend = 'phone_friend';
    case TwoAnswers = 'two_answers';

    public function label(): string
    {
        return match ($this) {
            self::Swap => 'تبديل السؤال',
            self::PhoneFriend => 'اتصال بصديق',
            self::TwoAnswers => 'إجابتان فقط',
        };
    }
}
