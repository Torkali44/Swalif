<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreCustomGameRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $name = $this->filled('title')
            ? $this->input('title')
            : $this->input('name');

        $teamOne = $this->filled('team_names.0')
            ? $this->input('team_names.0')
            : $this->input('team_one');

        $teamTwo = $this->filled('team_names.1')
            ? $this->input('team_names.1')
            : $this->input('team_two');

        $this->merge([
            'name' => is_string($name) ? trim($name) : $name,
            'team_one' => is_string($teamOne) ? trim($teamOne) : $teamOne,
            'team_two' => is_string($teamTwo) ? trim($teamTwo) : $teamTwo,
            'team_one_character_id' => $this->input('team_one_character_id') ?: $this->input('character_ids.0'),
            'team_two_character_id' => $this->input('team_two_character_id') ?: $this->input('character_ids.1'),
            'category_ids' => array_values(array_filter(array_map('intval', (array) $this->input('category_ids', [])))),
            'letter_grid_ids' => array_values(array_filter(array_map('intval', (array) $this->input('letter_grid_ids', [])))),
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'team_one' => ['required', 'string', 'max:50'],
            'team_two' => ['required', 'string', 'max:50', 'different:team_one'],
            'team_one_character_id' => ['required', 'integer', 'exists:characters,id'],
            'team_two_character_id' => [
                'required',
                'integer',
                'exists:characters,id',
                'different:team_one_character_id',
            ],
            'category_ids' => ['nullable', 'array'],
            'category_ids.*' => ['integer', 'distinct', 'exists:categories,id,is_active,1'],
            'letter_grid_ids' => ['nullable', 'array'],
            'letter_grid_ids.*' => ['integer', 'distinct', 'exists:letter_grids,id,is_active,1'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $cats = count((array) $this->input('category_ids', []));
            $grids = count((array) $this->input('letter_grid_ids', []));
            $total = $cats + $grids;

            if ($total < 4) {
                $validator->errors()->add('category_ids', 'يجب اختيار من 4 إلى 6 ألعاب (فئات أو شبكات حروف).');
            } elseif ($total > 6) {
                $validator->errors()->add('category_ids', 'لا يمكن اختيار أكثر من 6 ألعاب.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'name.required' => 'اسم اللعبة مطلوب.',
            'name.max' => 'اسم اللعبة لا يتجاوز 100 حرف.',
            'team_one.required' => 'اسم الفريق الأول مطلوب.',
            'team_two.required' => 'اسم الفريق الثاني مطلوب.',
            'team_two.different' => 'اسم الفريق الثاني يجب أن يختلف عن الأول.',
            'team_one_character_id.required' => 'اختر شخصية للفريق الأول.',
            'team_two_character_id.required' => 'اختر شخصية للفريق الثاني.',
            'team_two_character_id.different' => 'كل فريق لازم يختار شخصية مختلفة.',
            'category_ids.*.distinct' => 'لا يمكن تكرار نفس الفئة.',
            'category_ids.*.exists' => 'إحدى الفئات المختارة غير صالحة.',
            'letter_grid_ids.*.distinct' => 'لا يمكن تكرار نفس شبكة الحروف.',
            'letter_grid_ids.*.exists' => 'إحدى شبكات الحروف المختارة غير صالحة.',
        ];
    }
}
