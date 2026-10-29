<?php

namespace Modules\Restaurant\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Restaurant\Services\RestaurantBankingService;

class UpdateRegistrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user('admin')?->hasPermission('admin.restaurant.approvals.registration.update');
    }

    protected function prepareForValidation(): void
    {
        $values = [];
        foreach (array_keys($this->rules()) as $field) {
            if (is_string($this->input($field))) {
                $values[$field] = trim($this->input($field));
            }
        }
        $this->merge($values);
    }

    public function rules(): array
    {
        return [
            'step_key' => ['required', 'in:registration'],

            'name' => [
                'required',
                'string',
                'min:5',
                'max:100',
                'regex:/^[a-zA-Z ]+$/',
            ],

            'phone' => [
                'required',
                'string',
                'max:20',
                'regex:/^(?=(?:\D*\d){7,15}\D*$)\+?[0-9][0-9 ().-]*$/D',
            ],

            'description' => ['nullable', 'string', 'max:150'],
            'business_license' => ['nullable', 'string', 'max:255'],
            'address_line1' => ['nullable', 'string', 'max:100'],
            'address_line2' => ['nullable', 'string', 'max:100'],
            ...RestaurantBankingService::locationRules($this->only(['country_id', 'state_id', 'city_id'])),
            'postal_code' => ['nullable', 'string', 'max:80'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Enter the restaurant name.',
            'name.min' => 'The restaurant name must contain at least 5 characters.',
            'phone.required' => 'Enter a contact phone number.',
            'phone.regex' => 'Enter a valid phone number with 7 to 15 digits. You may include a leading +, spaces, parentheses, dots, or hyphens.',
        ];
    }

    public function attributes(): array
    {
        return ['name' => 'restaurant name', 'phone' => 'phone number', 'address_line1' => 'address line 1', 'address_line2' => 'address line 2'];
    }
}
