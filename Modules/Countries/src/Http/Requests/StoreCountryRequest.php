<?php

namespace Modules\Countries\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCountryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:16'],
            'name' => ['required', 'string', 'max:191'],
        ];
    }
}
