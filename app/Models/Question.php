<?php

namespace App\Models;

use App\Enums\Difficulty;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Question extends Model
{
    protected $fillable = [
        'category_id',
        'question_text',
        'image',
        'answer_image',
        'answer_text',
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

    public function correctAnswerText(): ?string
    {
        if (filled($this->answer_text)) {
            return $this->answer_text;
        }

        return optional($this->options->firstWhere('is_correct', true))->option_text;
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
