<?php

namespace Modules\Acl\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Acl\Models\SupportMessage;

class CustomerContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('sanctum')->check();
    }

    public function rules(): array
    {
        // $categories = implode(',', array_keys(SupportMessage::getCategories()));

        return [
            'category' => "required",
            'subject' => 'nullable|string|max:255',
            'message' => 'required|string|min:10|max:5000',
            'order_id' => 'nullable|exists:orders,id',
        ];
    }

    public function messages(): array
    {
        return [
            'category.required' => 'Please select a category',
            'subject.required' => 'Please enter a subject',
            'message.required' => 'Please enter your message',
            'message.min' => 'Message must be at least 10 characters',
            'message.max' => 'Message cannot exceed 5000 characters',
        ];
    }
}
