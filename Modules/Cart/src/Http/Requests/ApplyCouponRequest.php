<?php

namespace Modules\Cart\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ApplyCouponRequest extends FormRequest
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
     */
    public function rules(): array
    {
        return [
            'restaurant_id' => 'required|integer|exists:restaurants,id',
            'coupon_code' => [
                'required',
                'string',
                'max:40',
                'regex:/^[A-Z0-9]+$/', // Only uppercase letters and numbers
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'restaurant_id.required' => 'Restaurant ID is required',
            'restaurant_id.exists' => 'Selected restaurant does not exist',
            'coupon_code.required' => 'Coupon code is required',
            'coupon_code.string' => 'Coupon code must be a string',
            'coupon_code.max' => 'Coupon code cannot exceed 40 characters',
            'coupon_code.regex' => 'Coupon code can only contain uppercase letters and numbers',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'coupon_code' => strtoupper(trim($this->coupon_code ?? '')),
        ]);
    }
}