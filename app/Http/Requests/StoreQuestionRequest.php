<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
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
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $options = collect($this->input('options', []))
                ->map(fn ($v) => trim((string) $v))
                ->filter()
                ->values();

            $hasAnswerText = filled($this->input('answer_text'));

            if ($options->isEmpty() && ! $hasAnswerText) {
                $validator->errors()->add('answer_text', 'أضف نص الإجابة أو اختيارات للسؤال.');
            }

            if ($options->isNotEmpty() && $this->input('correct_option') === null) {
                $validator->errors()->add('correct_option', 'حدد الاختيار الصحيح.');
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
