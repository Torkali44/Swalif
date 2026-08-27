<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLetterGridGameRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $gridId = $this->input('letter_grid_id');
        $gridName = null;
        if ($gridId) {
            $gridName = \App\Models\LetterGrid::query()->whereKey($gridId)->value('name_ar');
        }

        $this->merge([
            'name' => $gridName ?: (is_string($this->input('name')) ? trim($this->input('name')) : 'شبكة الحروف'),
            'team_one' => is_string($this->input('team_one')) ? trim($this->input('team_one')) : $this->input('team_one'),
            'team_two' => is_string($this->input('team_two')) ? trim($this->input('team_two')) : $this->input('team_two'),
        ]);
    }

    public function rules(): array
    {
        return [
            'letter_grid_id' => ['required', 'integer', 'exists:letter_grids,id'],
            'name' => ['nullable', 'string', 'max:100'],
            'team_one' => ['required', 'string', 'max:50'],
            'team_two' => ['required', 'string', 'max:50', 'different:team_one'],
            'team_one_character_id' => ['required', 'integer', 'exists:characters,id'],
            'team_two_character_id' => [
                'required',
                'integer',
                'exists:characters,id',
                'different:team_one_character_id',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'letter_grid_id.required' => 'اختر شبكة الحروف.',
            'team_one.required' => 'اسم الفريق الأول مطلوب.',
            'team_two.required' => 'اسم الفريق الثاني مطلوب.',
            'team_two.different' => 'اسم الفريق الثاني يجب أن يختلف عن الأول.',
            'team_one_character_id.required' => 'اختر شخصية للفريق الأول.',
            'team_two_character_id.required' => 'اختر شخصية للفريق الثاني.',
            'team_two_character_id.different' => 'كل فريق لازم يختار شخصية مختلفة.',
        ];
    }
}
