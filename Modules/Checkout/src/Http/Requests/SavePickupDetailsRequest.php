<?php

namespace Modules\Checkout\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SavePickupDetailsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'preferred_delivery_date' => 'required|date',
            'preferred_delivery_time' => 'required|date_format:h:i A',
            'number_of_guests'        => 'required|integer|min:1',
            'phone'                   => 'nullable|string|max:20',
            'restaurant_id'           => 'nullable|integer',
        ];
    }
}
