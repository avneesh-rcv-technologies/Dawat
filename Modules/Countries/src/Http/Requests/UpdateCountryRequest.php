<?php

namespace Modules\Countries\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCountryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => ['sometimes', 'required', 'string', 'max:16'],
            'name' => ['sometimes', 'required', 'string', 'max:191'],
        ];
    }
}
