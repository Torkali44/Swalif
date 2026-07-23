<?php

namespace App\Enums;

enum QuestionType: string
{
    case Standard = 'standard';
    case ImageGuess = 'image_guess';
    case Puzzle = 'puzzle';
    case Match = 'match';
    case Complete = 'complete';
    case Order = 'order';
    case Video = 'video';
    case Audio = 'audio';

    public function label(): string
    {
        return match ($this) {
            self::Standard => 'عادي',
            self::ImageGuess => 'خمن الصورة',
            self::Puzzle => 'لغز',
            self::Match => 'توصيل',
            self::Complete => 'أكمل الناقص',
            self::Order => 'ترتيب',
            self::Video => 'فيديو',
            self::Audio => 'صوتي',
        };
    }

    public static function options(): array
    {
        return array_map(
            fn (self $type) => ['value' => $type->value, 'label' => $type->label()],
            self::cases()
        );
    }
}
