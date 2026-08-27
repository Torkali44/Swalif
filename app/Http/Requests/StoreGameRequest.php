<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreGameRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $charOneId = $this->input('team_one_character_id');
        $charTwoId = $this->input('team_two_character_id');

        $teamOne = $this->input('team_one');
        if (empty($teamOne) && $charOneId) {
            $teamOne = \App\Models\Character::query()->whereKey($charOneId)->value('name_ar');
        }

        $teamTwo = $this->input('team_two');
        if (empty($teamTwo) && $charTwoId) {
            $teamTwo = \App\Models\Character::query()->whereKey($charTwoId)->value('name_ar');
        }

        $this->merge([
            'team_one' => is_string($teamOne) ? trim($teamOne) : $teamOne,
            'team_two' => is_string($teamTwo) ? trim($teamTwo) : $teamTwo,
        ]);
    }

    public function rules(): array
    {
        return [
            'category_id' => ['required', 'exists:categories,id,is_active,1'],
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
            'team_one.required' => 'اسم الفريق الأول مطلوب.',
            'team_two.required' => 'اسم الفريق الثاني مطلوب.',
            'team_two.different' => 'اسم الفريق الثاني يجب أن يختلف عن الأول.',
            'team_one_character_id.required' => 'اختر شخصية للفريق الأول.',
            'team_two_character_id.required' => 'اختر شخصية للفريق الثاني.',
            'team_two_character_id.different' => 'كل فريق لازم يختار شخصية مختلفة.',
        ];
    }
}
