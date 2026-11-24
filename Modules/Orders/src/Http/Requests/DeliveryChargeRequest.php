<?php

namespace Modules\Orders\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DeliveryChargeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'restaurant_id' => ['required', 'integer', 'min:1'],
            'saved_address_id' => ['nullable', 'integer', 'min:1'],
            'latitude' => ['required_without:saved_address_id', Rule::prohibitedIf($this->filled('saved_address_id')), 'numeric', 'between:-90,90'],
            'longitude' => ['required_without:saved_address_id', Rule::prohibitedIf($this->filled('saved_address_id')), 'numeric', 'between:-180,180'],
            'order_subtotal' => ['required', 'numeric', 'decimal:0,2', 'min:0', 'max:99999999.99'],
        ];
    }
}
