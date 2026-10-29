<?php

namespace Modules\Restaurant\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Modules\Acl\Models\Admin;
use Illuminate\Validation\Rule;

class BankingDetailsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() instanceof Admin;
    }

    protected function prepareForValidation(): void
    {
        $values = [];
        foreach (array_keys($this->rules()) as $key) {
            if (is_string($this->input($key))) {
                $values[$key] = trim($this->input($key));
            }
        }
        $this->merge($values);
    }

    public function rules(): array
    {
        $required = $this->isMethod('PATCH') ? ['sometimes', 'required'] : ['required'];

        return [
            'account_holder_name' => [...$required, 'string', 'max:150'],
            'routing_number' => [...$required, 'string', 'regex:/^[0-9]{9}$/D'],
            'account_number' => [...$required, 'string', 'regex:/^[0-9]{4,17}$/D', 'confirmed'],
            'account_number_confirmation' => ['required_with:account_number', 'string'],
            'business_legal_name' => [...$required, 'string', 'max:150'],
            'doing_business_as' => ['sometimes', 'nullable', 'string', 'max:150'],
            'address' => [...$required, 'string', 'max:255'],
            'country_id' => [...$required, 'integer', Rule::exists('countries', 'id')],
            'state_id' => [...$required, 'integer', Rule::exists('country_states', 'id')],
            'city_id' => [...$required, 'integer', Rule::exists('cities', 'id')],
            'postal_code' => [...$required, 'string', 'max:20'],
            'restaurant_id' => ['prohibited'],
            'admin_id' => ['prohibited'],
            'city' => ['prohibited'],
            'state' => ['prohibited'],
            'status' => ['prohibited'],
            'reviewed_by' => ['prohibited'],
        ];
    }

    public function messages(): array
    {
        return [
            'account_holder_name.required' => 'Please enter the account holder’s name.',
            'routing_number.required' => 'Please enter your 9-digit routing number.',
            'routing_number.regex' => 'The routing number must contain exactly 9 digits.',
            'account_number.required' => 'Please enter your bank account number.',
            'account_number.regex' => 'The account number must contain 4 to 17 digits.',
            'account_number.confirmed' => 'The account numbers do not match. Please re-enter the same account number.',
            'account_number_confirmation.required_with' => 'Please re-enter your bank account number.',
            'business_legal_name.required' => 'Please enter the business legal entity name.',
            'address.required' => 'Please enter your business address.',
            'country_id.required' => 'Please select a country.',
            'country_id.exists' => 'Please select a valid country.',
            'state_id.required' => 'Please select a state.',
            'state_id.exists' => 'Please select a valid state.',
            'city_id.required' => 'Please select a city.',
            'city_id.exists' => 'Please select a valid city.',
            'postal_code.required' => 'Please enter your zip or postal code.',
        ];
    }

    public function attributes(): array
    {
        return [
            'account_holder_name' => 'account holder name', 'routing_number' => 'routing number',
            'account_number' => 'account number', 'account_number_confirmation' => 'account number confirmation',
            'business_legal_name' => 'business legal entity name', 'doing_business_as' => 'doing business as',
            'country_id' => 'country', 'state_id' => 'state', 'city_id' => 'city', 'postal_code' => 'zip or postal code',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'success' => false, 'message' => 'Please check your banking details.', 'errors' => $validator->errors(),
        ], 422));
    }
}
