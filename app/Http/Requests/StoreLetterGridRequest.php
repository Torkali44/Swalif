<?php

namespace App\Http\Requests;

use App\Support\LetterGridHelper;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreLetterGridRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $gridId = $this->route('letter_grid')?->id;

        return [
            'name_ar' => ['required', 'string', 'max:120'],
            'slug' => ['nullable', 'string', 'max:140', Rule::unique('letter_grids', 'slug')->ignore($gridId)],
            'description' => ['nullable', 'string', 'max:2000'],
            'image_path' => ['nullable', 'string', 'max:500'],
            'remove_image' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'cells' => ['required', 'array', 'min:2', 'max:40'],
            'cells.*.letter' => ['required', 'string', 'max:10'],
            'cells.*.row' => ['required', 'integer', 'min:0', 'max:20'],
            'cells.*.col' => ['required', 'integer', 'min:0', 'max:20'],
            'cells.*.question_text' => ['nullable', 'string', 'max:2000'],
            'cells.*.answer_text' => ['nullable', 'string', 'max:255'],
            'cells.*.is_active' => ['nullable', 'boolean'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $cells = collect($this->input('cells', []))
                ->map(function ($cell) {
                    return [
                        'letter' => trim((string) data_get($cell, 'letter', '')),
                        'question_text' => trim((string) data_get($cell, 'question_text', '')),
                        'answer_text' => trim((string) data_get($cell, 'answer_text', '')),
                    ];
                })
                ->all();

            foreach (LetterGridHelper::validateCells($cells) as $message) {
                $validator->errors()->add('cells', $message);
            }
        });
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active'),
            'remove_image' => $this->boolean('remove_image'),
        ]);
    }

    public function messages(): array
    {
        return [
            'name_ar.required' => 'اسم الشبكة مطلوب.',
            'cells.required' => 'أضف حروف الشبكة.',
            'cells.min' => 'أضف حرفين على الأقل.',
        ];
    }
}
