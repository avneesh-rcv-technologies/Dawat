<?php

namespace Modules\Tax\Http\Requests;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Foundation\Http\FormRequest;
use Modules\Tax\Enums\TaxType;

use Illuminate\Validation\Rule;

class TaxRateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:55',
            'rate_value' => 'required|numeric|min:0',
            'type' => ['required', Rule::in(array_column(TaxType::cases(), 'value'))],
            'country_id' => 'required|exists:countries,id',
            'state' => 'nullable|string|max:255',
            'postcode' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'is_active' => 'boolean',
            'priority' => 'integer|min:0'
        ];
    }

    public function messages()
    {
        return [
            'country_id.required' => 'Please select a country.',
            'country_id.exists' => 'The selected country is invalid.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'status' => false,
            'errors' => $validator->errors(),
        ]));
    }
}