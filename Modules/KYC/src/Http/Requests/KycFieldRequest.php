<?php

namespace Modules\KYC\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class KycFieldRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'field_name'                        => 'required|string|max:150',
            'field_key'                         => 'required|string|max:150|regex:/^[a-z0-9_]+$/',
            'field_type'                        => 'required|in:text,number,file,date,select',
            'is_required'                       => 'boolean',
            'options'                           => 'nullable|array',
            'order_index'                       => 'integer|min:0',

            // validation_rules — shared
            'validation_rules'                  => 'nullable|array',

            // text
            'validation_rules.min_length'       => 'nullable|integer|min:0',
            'validation_rules.max_length'       => 'nullable|integer|min:1',
            'validation_rules.format'           => 'nullable|string|in:none,letters_only,numbers_only,alphanumeric,alphanumeric_strict,email',

            // number
            'validation_rules.min'              => 'nullable|numeric',
            'validation_rules.max'              => 'nullable|numeric',

            // file
            'validation_rules.max_size_kb'      => 'nullable|integer|min:1',
            'validation_rules.allowed_types'    => 'nullable',

            // date
            'validation_rules.min_date'         => 'nullable|date',
            'validation_rules.max_date'         => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'field_name.required'  => 'Field name is required',
            'field_key.required'   => 'Field key is required',
            'field_key.regex'      => 'Field key must contain only lowercase letters, numbers, and underscores',
            'field_type.required'  => 'Field type is required',
            'field_type.in'        => 'Invalid field type selected',
        ];
    }
}
