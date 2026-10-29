<?php

namespace Modules\Twilio\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SmsTemplateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:150',
            'subject' => 'required|string',
            'body' => 'nullable|string',
            'variables' => 'nullable|json',
            'is_active' => 'nullable|in:0,1',
        ];
    }
}
