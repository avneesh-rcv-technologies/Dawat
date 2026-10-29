<?php

namespace Modules\Twilio\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrUpdateTwilioRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'phone_number' => 'required|string',
            'message' => 'required|string',
            // Add more rules as needed
        ];
    }
}
