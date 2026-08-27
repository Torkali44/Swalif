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
    case WordBuild = 'word_build';
    case Video = 'video';
    case Audio = 'audio';

    public function label(): string
    {
            return match ($this) {
            self::Standard => 'أختياري',
            self::ImageGuess =>'خمن الصورة( لغز )',
            self::Puzzle => 'جواب واحد',
            self::Match => 'توصيل',
            self::Complete => 'أكمل الناقص',
            self::Order => 'ترتيب',
            self::WordBuild => 'رتبها',
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
