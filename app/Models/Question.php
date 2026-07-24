<?php

namespace App\Models;

use App\Enums\Difficulty;
use App\Enums\QuestionType;
use App\Support\PublicMedia;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    protected $fillable = [
        'category_id',
        'type',
        'question_text',
        'image',
        'answer_image',
        'answer_text',
        'meta',
        'level',
        'points',
        'time_limit',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'level' => Difficulty::class,
            'meta' => 'array',
        ];
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function options()
    {
        return $this->hasMany(QuestionOption::class);
    }

    public function displayPoints(): int
    {
        if ($this->level instanceof Difficulty) {
            return $this->level->points();
        }

        $level = Difficulty::tryFrom((string) $this->level);

        return $level?->points() ?? (int) $this->points;
    }

    protected static function booted(): void
    {
        static::saving(function (Question $question) {
            $level = $question->level instanceof Difficulty
                ? $question->level
                : Difficulty::tryFrom((string) $question->level);

            if ($level) {
                $question->points = $level->points();
            }
        });
    }

    public function imageUrl(): ?string
    {
        if ($this->isVideo() || $this->isAudio()) {
            return null;
        }

        return $this->publicUrl($this->image);
    }

    public function mediaUrl(): ?string
    {
        return $this->publicUrl($this->image);
    }

    public function isVideo(): bool
    {
        return ($this->type ?? '') === QuestionType::Video->value;
    }

    public function isAudio(): bool
    {
        return ($this->type ?? '') === QuestionType::Audio->value;
    }

    public function answerImageUrl(): ?string
    {
        return $this->publicUrl($this->answer_image);
    }

    public function typeLabel(): string
    {
        return QuestionType::tryFrom($this->type ?? QuestionType::Standard->value)?->label()
            ?? QuestionType::Standard->label();
    }

    public function orderItems(): array
    {
        return collect(data_get($this->meta, 'order_items', []))
            ->map(fn ($item) => trim((string) $item))
            ->filter()
            ->values()
            ->all();
    }

    public function matchPairs(): array
    {
        return collect(data_get($this->meta, 'match_pairs', []))
            ->map(function ($pair) {
                return [
                    'left' => trim((string) data_get($pair, 'left', '')),
                    'right' => trim((string) data_get($pair, 'right', '')),
                ];
            })
            ->filter(fn ($pair) => filled($pair['left']) && filled($pair['right']))
            ->values()
            ->all();
    }

    public function correctAnswerText(): ?string
    {
        return match ($this->type ?? QuestionType::Standard->value) {
            QuestionType::ImageGuess->value,
            QuestionType::Puzzle->value,
            QuestionType::Complete->value,
            QuestionType::Video->value,
            QuestionType::Audio->value => filled($this->answer_text) ? $this->answer_text : null,
            QuestionType::Order->value => filled($this->orderItems())
                ? implode(' → ', $this->orderItems())
                : null,
            QuestionType::Match->value => filled($this->matchPairs())
                ? collect($this->matchPairs())
                    ->map(fn ($pair) => $pair['left'].' ↔ '.$pair['right'])
                    ->implode(' | ')
                : null,
            default => optional($this->options->firstWhere('is_correct', true))->option_text
                ?: (filled($this->answer_text) ? $this->answer_text : null),
        };
    }

    public function hasChoices(): bool
    {
        return $this->options->filter(fn ($o) => filled($o->option_text))->isNotEmpty();
    }

    private function publicUrl(?string $path): ?string
    {
        return PublicMedia::url($path);
    }
}
