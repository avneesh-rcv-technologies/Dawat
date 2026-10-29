<?php

namespace Modules\Acl\Http\Requests\admin;

use App\Rules\StrongPassword;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRequest extends FormRequest
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
        $adminId = $this->route('admin') ?? $this->route('id');
        
        return [
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                Rule::unique('admins', 'email')->ignore($adminId),
            ],
            'password' => ['nullable', 'string', 'confirmed', new StrongPassword()],
            'status' => 'nullable|in:active,inactive',
            'role_id' => 'nullable|exists:roles,id',
        ];
    }

    /**
     * Get custom attribute names for validator errors.
     */
    public function attributes(): array
    {
        return [
            'name' => 'Name',
            'email' => 'Email Address',
            'password' => 'Password',
            'status' => 'Status',
            'role_id' => 'Role',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'email.unique' => 'This email address is already taken.',
            'password.confirmed' => 'The password confirmation does not match.',
        ];
    }
}
