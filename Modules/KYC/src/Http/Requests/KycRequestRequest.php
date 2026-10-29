<?php

namespace Modules\KYC\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class KycRequestRequest extends FormRequest
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
    public function rules(): array
    {
        return [
            'kyc_type_id' => 'required|exists:kyc_types,id',
            'title' => 'required|string|max:255',
            'message' => 'nullable|string',
            'due_date' => 'nullable|date|after:today',
            'target.type' => 'required|in:single,multiple,group,all',
            'target.user_id' => 'required_if:target.type,single|integer',
            'target.user_ids' => 'required_if:target.type,multiple|array',
            'target.user_ids.*' => 'integer',
            'target.group' => 'required_if:target.type,group|string'
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'kyc_type_id.required' => 'Please select a KYC type',
            'kyc_type_id.exists' => 'Selected KYC type does not exist',
            'title.required' => 'Request title is required',
            'due_date.after' => 'Due date must be a future date',
            'target.type.required' => 'Please select notification target type',
            'target.user_id.required_if' => 'Please select a user',
            'target.user_ids.required_if' => 'Please select at least one user',
            'target.group.required_if' => 'Please select a user group'
        ];
    }
}
