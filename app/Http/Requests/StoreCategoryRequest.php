<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name_ar' => ['required', 'string', 'max:100'],
            'name_en' => ['nullable', 'string', 'max:100'],
            'classification_id' => ['required', 'exists:classifications,id'],
            'icon' => ['nullable', 'string', 'max:20'],
            'description' => ['nullable', 'string', 'max:500'],
            'sort_order' => ['required', 'integer', 'min:1'],
            'is_active' => ['nullable', 'boolean'],
            'image'          => ['nullable', 'image', 'max:5120'],
            'image_path'     => ['nullable', 'string', 'max:500'],
            'remove_image'   => ['nullable', 'boolean'],
        ];
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
            'name_ar.required' => 'الاسم العربي مطلوب.',
            'classification_id.required' => 'اختر التصنيف.',
            'classification_id.exists' => 'التصنيف المحدد غير موجود.',
            'sort_order.required' => 'ترتيب الظهور مطلوب.',
            'sort_order.min' => 'ترتيب الظهور يجب أن يبدأ من 1.',
            'image.image' => 'الملف يجب أن يكون صورة.',
            'image.max' => 'حجم الصورة يجب ألا يتجاوز 5 ميجابايت.',
        ];
    }
}
