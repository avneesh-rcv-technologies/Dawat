<?php

namespace Modules\KYC\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\KYC\Models\KycType;

class KycSubmissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'type' => 'required|exists:kyc_types,id',
        ];

        if ($this->has('type')) {
            $kycType = KycType::with('fields')->find($this->input('type'));

            if ($kycType) {
                foreach ($kycType->fields as $field) {
                    $rules[$field->field_key] = implode('|', $field->getValidationRules());
                }
            }
        }

        return $rules;
    }

    public function messages(): array
    {
        $messages = [
            'type.required' => 'KYC type is required',
            'type.exists'   => 'Selected KYC type does not exist',
        ];

        if ($this->has('type')) {
            $kycType = KycType::with('fields')->find($this->input('type'));

            if ($kycType) {
                foreach ($kycType->fields as $field) {
                    // Merge per-field messages built from stored validation_rules
                    $messages = array_merge($messages, $field->getValidationMessages());
                }
            }
        }

        return $messages;
    }
}
