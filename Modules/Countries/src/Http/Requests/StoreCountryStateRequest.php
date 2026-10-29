<?php

namespace Modules\Countries\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCountryStateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'country_id' => ['required', 'integer', 'exists:countries,id'],
            'country_code' => ['sometimes', 'nullable', 'string', 'max:16'],
            'code' => ['required', 'string', 'max:32'],
            'default_name' => ['required', 'string', 'max:191'],
        ];
    }
}
