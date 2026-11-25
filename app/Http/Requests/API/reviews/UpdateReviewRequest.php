<?php

namespace App\Http\Requests\API\reviews;

use Illuminate\Foundation\Http\FormRequest;

class UpdateReviewRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $hasRouteId = $this->route('id') !== null;

        return [
            'review_id' => [$hasRouteId ? 'nullable' : 'required_without:id', 'integer'],
            'id'        => ['nullable', 'integer'],
            'comment'   => ['nullable', 'string', 'min:3'],
            'rate'      => ['nullable', 'integer', 'in:1,2,3,4,5'],
        ];
    }

    public function messages(): array
    {
        return [
            'review_id.required_without' => __('this field is required'),
            'comment.min'                => __('must At least 3 letters'),
            'rate.in'                    => __('only 5 stars '),
        ];
    }
}
