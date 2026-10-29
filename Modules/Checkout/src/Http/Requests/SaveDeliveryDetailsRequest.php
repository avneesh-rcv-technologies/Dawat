<?php

namespace Modules\Checkout\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SaveDeliveryDetailsRequest extends FormRequest
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
            'address_line_1'          => 'required|string|max:255',
            'address_line_2'          => 'nullable|string|max:255',
            'city_id'                 => 'nullable|integer|exists:cities,id',
            'state_id'                => 'nullable|integer|exists:country_states,id',
            'country_id'              => 'nullable|integer|exists:countries,id',
            'zip_code'                => 'required|string|max:20',
            'latitude'                => 'nullable|numeric|between:-90,90',
            'longitude'               => 'nullable|numeric|between:-180,180',
            'city_name'               => 'nullable|string|max:50',
            'floor'                   => 'nullable|string|max:50',
        ];
    }
}
