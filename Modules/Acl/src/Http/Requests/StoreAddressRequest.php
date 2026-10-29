<?php

namespace Modules\Acl\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAddressRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'address_type' => 'nullable|string|in:home,work,other',
            'address_line_1' => 'required|string|max:255',
            'address_line_2' => 'nullable|string|max:255',
            'city_id' => 'nullable|integer|exists:cities,id',
            'state_id' => 'nullable|integer|exists:country_states,id',
            'country_id' => 'nullable|integer|exists:countries,id',
            'zip_code' => 'required|string|max:20',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'is_default' => 'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'address_line_1.required' => 'Address line 1 is required',
            'city_id.exists' => 'Selected city is invalid',
            'state_id.exists' => 'Selected state is invalid',
            'country_id.exists' => 'Selected country is invalid',
            'zip_code.required' => 'Zip code is required',
        ];
    }
}
