<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCustomGameRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Auth check done in controller
    }

    protected function prepareForValidation(): void
    {
        $name    = $this->filled('title') ? $this->input('title') : ($this->filled('name') ? $this->input('name') : 'تحدي سوالف الخاص');
        $teamOne = $this->filled('team_names.0') ? $this->input('team_names.0') : ($this->filled('team_one') ? $this->input('team_one') : 'الفريق الأول');
        $teamTwo = $this->filled('team_names.1') ? $this->input('team_names.1') : ($this->filled('team_two') ? $this->input('team_two') : 'الفريق الثاني');

        $this->merge([
            'name'     => $name,
            'team_one' => $teamOne,
            'team_two' => $teamTwo,
        ]);
    }

    public function rules(): array
    {
        return [
            'name'           => ['nullable', 'string', 'max:100'],
            'team_one'       => ['nullable', 'string', 'max:50'],
            'team_two'       => ['nullable', 'string', 'max:50'],
            'category_ids'   => ['required', 'array', 'min:4', 'max:6'],
            'category_ids.*' => ['integer', 'distinct', 'exists:categories,id,is_active,1'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'           => 'اسم اللعبة مطلوب.',
            'name.max'                => 'اسم اللعبة لا يتجاوز 100 حرف.',
            'team_one.required'       => 'اسم الفريق الأول مطلوب.',
            'team_two.required'       => 'اسم الفريق الثاني مطلوب.',
            'category_ids.required'   => 'يجب اختيار من 4 إلى 6 فئات.',
            'category_ids.min'        => 'يجب اختيار 4 فئات على الأقل.',
            'category_ids.max'        => 'لا يمكن اختيار أكثر من 6 فئات.',
            'category_ids.*.distinct' => 'لا يمكن تكرار نفس الفئة.',
            'category_ids.*.exists'   => 'إحدى الفئات المختارة غير صالحة.',
        ];
    }
}
