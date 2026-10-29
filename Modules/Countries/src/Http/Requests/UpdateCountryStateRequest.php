<?php

namespace Modules\Countries\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCountryStateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'country_id' => ['sometimes', 'required', 'integer', 'exists:countries,id'],
            'country_code' => ['sometimes', 'nullable', 'string', 'max:16'],
            'code' => ['sometimes', 'required', 'string', 'max:32'],
            'default_name' => ['sometimes', 'required', 'string', 'max:191'],
        ];
    }
}
