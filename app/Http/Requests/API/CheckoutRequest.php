<?php

namespace App\Http\Requests\API;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class CheckoutRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Validation rules
     */
    public function rules(): array
    {
        return [
            'user_name' => [
                'required',
                'string',
                'min:3',
                'max:255',
            ],
            'user_phone' => [
                'required',
                'string',
                'regex:/^(?:\+20|0)(10|11|12|15)[0-9]{8}$/',
            ],
            'user_address' => [
                'required',
                'string',
                'min:10',
            ],
            'governorate_id' => [
                'required',
            ],
            'coupon_code' => [
                'nullable',
                'string',
                'max:50',
            ],
            'email' => [
                'nullable',
                'email',
                'max:255',
            ],
            'user_email' => [
                'nullable',
                'email',
                'max:255',
            ],
            'marketing_opt_in' => [
                'nullable',
                'boolean',
            ],
            'items' => [
                'nullable',
                'array',
                'min:1',
            ],
            'items.*.property_id' => [
                'required_without:items.*.id',
                'integer',
                'exists:properties,id',
            ],
            'items.*.id' => [
                'required_without:items.*.property_id',
                'integer',
                'exists:properties,id',
            ],
            'items.*.quantity' => [
                'required_with:items',
                'integer',
                'min:1',
            ],
        ];
    }

    /**
     * Custom validation messages
     */
    public function messages(): array
    {
        return [
            "user_name.required"  => "User name is required",
            "user_name.min"       => "User name must be at least 3 characters",

            "user_phone.required" => "Phone number is required",
            "user_phone.regex"    => "Phone number must be a valid Egyptian number starting with +20",

            "user_address.required" => "Address is required",
            "user_address.min"      => "Address must be at least 10 characters",
        ];
    }

    /**
     * Return JSON response on validation error (API)
     */
}