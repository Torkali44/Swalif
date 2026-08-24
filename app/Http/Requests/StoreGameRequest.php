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
        $this->merge([
            'team_one' => $this->filled('team_one') ? $this->input('team_one') : 'الفريق الأول',
            'team_two' => $this->filled('team_two') ? $this->input('team_two') : 'الفريق الثاني',
        ]);
    }

    public function rules(): array
    {
        return [
            'category_id' => ['required', 'exists:categories,id,is_active,1'],
            'name'        => ['nullable', 'string', 'max:100'],
            'team_one'    => ['nullable', 'string', 'max:50'],
            'team_two'    => ['nullable', 'string', 'max:50'],
        ];
    }
}
