<?php

namespace Modules\Tax\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;


class TaxRuleRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'tax_category_id' => 'required|exists:tax_categories,id',
            'tax_rate_id' => 'required|exists:tax_rates,id',
            'priority' => 'integer|min:0'
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