<?php

namespace Modules\Orders\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOrderRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user()->can('update', $this->order);
    }

    public function rules()
    {
        // Get valid status codes from statuses table
        $validStatuses = fn_get_order_statuses('O')->pluck('status_code')->toArray();
        
        return [
            'status' => 'sometimes|in:' . implode(',', $validStatuses),
            'payment_status' => 'sometimes|in:unpaid,paid,refunded,partially_refunded',
            'notes' => 'nullable|string',
        ];
    }
}