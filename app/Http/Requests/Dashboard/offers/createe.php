<?php
namespace App\Http\Requests\Dashboard\offers;
use App\Models\{Offer,Property};
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class createe extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    public function rules(): array
    {
        return [
            'property_id' => ['required', 'exists:properties,id'],
            'discount_price' => ['required', 'numeric', 'min:0'],
            'start' => ['required', 'date'],
            'end' => ['required', 'date', 'after_or_equal:start'],
        ];
    }
    
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $propertyId = $this->input('property_id');
            $newStart = $this->input('start');
            $newEnd = $this->input('end');

            if ($propertyId && $newStart && $newEnd) {
                // Ensure consistent formatting
                $startDate = Carbon::parse($newStart)->format('Y-m-d H:i:s');
                $endDate = Carbon::parse($newEnd)->format('Y-m-d H:i:s');

                // Check for overlapping offers for the same property_id
                $conflictingOffer = Offer::where('property_id', $propertyId)
                    ->where(function ($query) use ($startDate, $endDate) {
                        $query->where('start', '<=', $endDate)
                              ->where('end', '>=', $startDate);
                    })
                    ->first();

                if ($conflictingOffer) {
                    $validator->errors()->add('start', __('Schedule overlap: An offer already exists in this period (:start - :end)', ['start' => $conflictingOffer->start, 'end' => $conflictingOffer->end]));
                    $validator->errors()->add('end', __('The time period overlaps with an existing offer.'));
                }
            }

            // Prevent start dates in the past
            if ($newStart && Carbon::parse($newStart)->isPast()) {
                 $validator->errors()->add('start', __('Start date cannot be in the past.'));
            }
        });
    }
    
    
    public function messages(): array
    {
        return [
            'property_id.required' => __('Please select a product variant.'),
            'property_id.exists' => __('Selected product variant does not exist.'),
            'discount_price.required' => __('Discount is required.'),
            'discount_price.numeric' => __('Discount must be a number.'),
            'discount_price.min' => __('Discount must be at least 0.'),
            'start.required' => __('Start date is required.'),
            'start.date' => __('Start date must be a valid date.'),
            'end.required' => __('End date is required.'),
            'end.date' => __('End date must be a valid date.'),
            'end.after_or_equal' => __('End date must be equal or after start date.'),
        ];
    }
}
