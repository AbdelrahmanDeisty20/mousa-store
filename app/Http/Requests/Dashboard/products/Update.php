<?php

namespace App\Http\Requests\Dashboard\products;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class Update extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name_en' => [
                'required',
                'string',
                'max:255',
                Rule::unique('products', 'name_en')->ignore($this->route('product')),
            ],
            'name_ar' => [
                'required',
                'string',
                'max:255',
                Rule::unique('products', 'name_ar')->ignore($this->route('product')),
            ],
            'desc_en' => ['required', 'string'],
            'desc_ar' => ['required', 'string'],
            'category_id' => ['required', 'exists:categories,id'],
            'brand_id' => ['required', 'exists:brands,id'],
            'variants' => 'nullable|array',
            'variants.*.price' => 'required|numeric|min:0',
            'variants.*.stock' => 'required|integer|min:0',
            'variants.*.attributes' => [
                'nullable',
                'array',
                function ($attribute, $value, $fail) {
                    $keys = collect($value)->pluck('key_en')->filter();
                    if ($keys->count() !== $keys->unique()->count()) {
                        $fail(__('Each variant must have unique attribute keys.'));
                    }
                }
            ],
            'variants.*.attributes.*.key_en' => 'required|string|max:255',
            'variants.*.attributes.*.key_ar' => 'required|string|max:255',
            'variants.*.attributes.*.value_en' => 'required|string|max:255',
            'variants.*.attributes.*.value_ar' => 'required|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'name_en.required' => __('English name is required.'),
            'name_en.unique' => __('The English name has already been taken.'),
            'name_ar.required' => __('Arabic name is required.'),
            'name_ar.unique' => __('The Arabic name has already been taken.'),
            'desc_en.required' => __('English description is required.'),
            'desc_ar.required' => __('Arabic description is required.'),
            'category_id.required' => __('Category is required.'),
            'category_id.exists' => __('Selected category does not exist.'),
            'brand_id.required' => __('Brand is required.'),
            'brand_id.exists' => __('Selected brand does not exist.'),
        ];
    }
}
