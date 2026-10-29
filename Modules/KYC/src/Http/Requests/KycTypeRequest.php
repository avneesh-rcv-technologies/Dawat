<?php

namespace Modules\KYC\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\NoScripts;

class KycTypeRequest extends FormRequest
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
    // public function rules(): array
    // {
    //     $rules = [
    //         'name' => 'required|string|max:150',
    //         'description' => 'nullable|string',
    //         'is_active' => 'boolean',
    //         'fields' => 'required|array|min:1',
    //         'fields.*.field_name' => 'required|string|max:150',
    //         'fields.*.field_key' => 'required|string|max:150|regex:/^[a-z0-9_]+$/',
    //         'fields.*.field_type' => 'required|in:text,number,file,date,select',
    //         'fields.*.is_required' => 'boolean',
    //         'fields.*.options' => 'nullable|array',
    //         'fields.*.order_index' => 'integer|min:0'
    //     ];

    //     // For update, make name unique except for current record
    //     if ($this->route('type')) {
    //         $rules['name'] .= '|unique:kyc_types,name,' . $this->route('type');
    //     } else {
    //         $rules['name'] .= '|unique:kyc_types,name';
    //     }

    //     return $rules;
    // }
    public function rules(): array
    {
        $rules = [
            'name'                 => ['required', 'string', 'max:150', new NoScripts],
            'type'                 => 'nullable|in:kyc,compliance',
            'description'          => ['nullable', 'string', new NoScripts],
            'is_active'            => 'boolean',
        ];

        $isUpdate = $this->isMethod('PUT') || $this->isMethod('PATCH') || $this->route('type') || $this->route('id');

        if (!$isUpdate) {
            $rules['fields']               = 'required|array|min:1';
            $rules['fields.*.field_name']  = ['required', 'string', 'max:150', new NoScripts];
            $rules['fields.*.field_key']   = 'required|string|max:150|regex:/^[a-z0-9_]+$/';
            $rules['fields.*.field_type']  = 'required|in:text,number,file,date,select';
            $rules['fields.*.is_required'] = 'boolean';
            $rules['fields.*.options']     = 'nullable|array';
            $rules['fields.*.order_index'] = 'integer|min:0';

            // validation_rules per field
            $rules['fields.*.validation_rules']                = 'nullable|array';
            $rules['fields.*.validation_rules.min_length']     = 'nullable|integer|min:0';
            $rules['fields.*.validation_rules.max_length']     = 'nullable|integer|min:1';
            $rules['fields.*.validation_rules.format']         = 'nullable|string|in:none,letters_only,numbers_only,alphanumeric,alphanumeric_strict,email';
            $rules['fields.*.validation_rules.min']            = 'nullable|numeric';
            $rules['fields.*.validation_rules.max']            = 'nullable|numeric';
            $rules['fields.*.validation_rules.max_size_kb']    = 'nullable|integer|min:1';
            $rules['fields.*.validation_rules.allowed_types']  = 'nullable';
            $rules['fields.*.validation_rules.min_date']       = 'nullable|date';
            $rules['fields.*.validation_rules.max_date']       = 'nullable|date';
        }

        $typeId = $this->route('type') ?: $this->route('id');
        if ($typeId) {
            $rules['name'][] = 'unique:kyc_types,name,' . $typeId;
        } else {
            $rules['name'][] = 'unique:kyc_types,name';
        }
        
        return $rules;
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $this->validateUniqueFieldKeys($validator);
            $this->validateSelectFieldOptions($validator);
        });
    }

    /**
     * Validate that field keys are unique within the same KYC type
     */
    protected function validateUniqueFieldKeys($validator)
    {
        $isUpdate = $this->isMethod('PUT') || $this->isMethod('PATCH') || $this->route('type') || $this->route('id');
        if ($isUpdate) return;

        $fields = $this->input('fields', []);
        $fieldKeys = array_column($fields, 'field_key');

        if (count($fieldKeys) !== count(array_unique($fieldKeys))) {
            $validator->errors()->add('fields', 'Field keys must be unique within the same KYC Type');
        }
    }

    /**
     * Validate that select fields have options
     */
    protected function validateSelectFieldOptions($validator)
    {
        $isUpdate = $this->isMethod('PUT') || $this->isMethod('PATCH') || $this->route('type') || $this->route('id');
        if ($isUpdate) return;

        $fields = $this->input('fields', []);

        foreach ($fields as $index => $field) {
            if ($field['field_type'] === 'select') {
                if (empty($field['options']) || !is_array($field['options']) || count($field['options']) === 0) {
                    $validator->errors()->add("fields.{$index}.options", 'Select field must have at least one option');
                }
            }
        }
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'KYC Type name is required',
            'name.unique' => 'This KYC Type name already exists',
            'fields.required' => 'At least one field must be configured for the KYC Type',
            'fields.min' => 'At least one field must be configured for the KYC Type',
            'fields.*.field_name.required' => 'Field name is required',
            'fields.*.field_key.required' => 'Field key is required',
            'fields.*.field_key.regex' => 'Field key must contain only lowercase letters, numbers, and underscores',
            'fields.*.field_type.required' => 'Field type is required',
            'fields.*.field_type.in' => 'Invalid field type selected'
        ];
    }
}
