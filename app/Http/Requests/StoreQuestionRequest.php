<?php

namespace App\Http\Requests;

use App\Models\Question;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreQuestionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id' => ['required', 'exists:categories,id'],
            'type' => ['required', Rule::in(['standard', 'image_guess', 'puzzle', 'match', 'complete', 'order'])],
            'question_text' => ['required', 'string', 'max:2000'],
            'answer_text' => ['nullable', 'string', 'max:2000'],
            'level' => ['required', 'in:easy,medium,hard'],
            'points' => ['required', 'integer', 'in:200,400,600'],
            'time_limit' => ['nullable', 'integer', 'min:10', 'max:300'],
            'is_active' => ['nullable', 'boolean'],
            'image' => ['nullable', 'image', 'max:5120'],
            'answer_image' => ['nullable', 'image', 'max:5120'],
            'remove_image' => ['nullable', 'boolean'],
            'remove_answer_image' => ['nullable', 'boolean'],
            'options' => ['nullable', 'array', 'max:4'],
            'options.*' => ['nullable', 'string', 'max:255'],
            'correct_option' => ['nullable', 'integer', 'min:0', 'max:3'],
            'order_items' => ['nullable', 'array', 'max:12'],
            'order_items.*' => ['nullable', 'string', 'max:255'],
            'match_pairs' => ['nullable', 'array', 'max:12'],
            'match_pairs.*.left' => ['nullable', 'string', 'max:255'],
            'match_pairs.*.right' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $type = (string) $this->input('type', 'standard');
            $question = $this->route('question');
            $existingQuestion = $question instanceof Question ? $question : null;

            $options = collect($this->input('options', []))
                ->map(fn ($v) => trim((string) $v))
                ->values();

            $filledOptions = $options->filter();

            $orderItems = collect($this->input('order_items', []))
                ->map(fn ($v) => trim((string) $v))
                ->filter()
                ->values();

            $matchPairs = collect($this->input('match_pairs', []))
                ->map(function ($pair) {
                    return [
                        'left' => trim((string) data_get($pair, 'left', '')),
                        'right' => trim((string) data_get($pair, 'right', '')),
                    ];
                })
                ->values();

            $hasAnswerText = filled($this->input('answer_text'));
            $hasImage = $this->hasFile('image') || filled($existingQuestion?->image);

            if ($type === 'standard') {
                if ($filledOptions->count() < 2) {
                    $validator->errors()->add('options', 'أضف خيارين على الأقل للسؤال العادي.');
                }

                if (! $this->filled('correct_option')) {
                    $validator->errors()->add('correct_option', 'حدد الاختيار الصحيح من بين الاختيارات المكتوبة.');
                } elseif (! in_array((int) $this->input('correct_option'), $filledOptions->keys()->all(), true)) {
                    $validator->errors()->add('correct_option', 'حدد الاختيار الصحيح من بين الاختيارات المكتوبة.');
                }

                return;
            }

            if ($type === 'image_guess') {
                if (! $hasImage) {
                    $validator->errors()->add('image', 'ارفع صورة السؤال لهذا النوع.');
                }

                if (! $hasAnswerText) {
                    $validator->errors()->add('answer_text', 'اكتب الإجابة النصية لعرضها للمستخدم.');
                }

                return;
            }

            if ($type === 'order') {
                if ($orderItems->count() < 2) {
                    $validator->errors()->add('order_items', 'أضف عنصرين على الأقل للترتيب.');
                }

                return;
            }

            if ($type === 'match') {
                $validPairs = $matchPairs->filter(fn ($pair) => filled($pair['left']) && filled($pair['right']));

                if ($validPairs->count() < 2) {
                    $validator->errors()->add('match_pairs', 'أضف زوجين على الأقل في التوصيل.');
                }

                if ($matchPairs->contains(fn ($pair) => filled($pair['left']) !== filled($pair['right']))) {
                    $validator->errors()->add('match_pairs', 'كل زوج في التوصيل لازم يكون له طرفين مكتملين.');
                }

                return;
            }

            if (in_array($type, ['puzzle', 'complete'], true) && ! $hasAnswerText) {
                $validator->errors()->add('answer_text', 'اكتب الإجابة النصية لهذا النوع.');
            }
        });
    }

    protected function prepareForValidation(): void
    {
        $level = $this->input('level');
        $this->merge([
            'is_active' => $this->boolean('is_active'),
            'remove_image' => $this->boolean('remove_image'),
            'remove_answer_image' => $this->boolean('remove_answer_image'),
            'time_limit' => $this->input('time_limit', config('game.default_time_limit', 60)),
            'points' => $this->input('points') ?: (config('game.points_map.'.$level) ?? 200),
        ]);
    }
}
