<?php

namespace App\Models;

use App\Enums\Difficulty;
use App\Enums\QuestionType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

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

    public function imageUrl(): ?string
    {
        return $this->publicUrl($this->image);
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
            QuestionType::Complete->value => filled($this->answer_text) ? $this->answer_text : null,
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
        if (! $path) {
            return null;
        }

        // Relative URL avoids APP_URL host mismatch (localhost vs 127.0.0.1)
        if (Storage::disk('public')->exists($path)) {
            return '/storage/'.ltrim($path, '/');
        }

        return null;
    }
}
