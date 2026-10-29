<?php

namespace Modules\Orders\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OrderRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        // Get valid status codes from statuses table
        $validStatuses = fn_get_order_statuses('O')->pluck('status_code')->toArray();
        
        $rules = [
            'customer_id' => 'required|integer',
            'customer_type' => 'required|string|in:App\\Models\\User,Modules\\Acl\\Models\\Customer',
            'vendor_id' => 'required|integer',
            'vendor_type' => 'required|string',
            'status' => 'required|string|in:' . implode(',', $validStatuses),
            'payment_method' => 'required|string',
            'payment_status' => 'required|string',
            'notes' => 'nullable|string',
            'billing_address' => 'required|array',
            'billing_address.first_name' => 'required|string',
            'billing_address.last_name' => 'required|string',
            'billing_address.address_1' => 'required|string',
            'billing_address.city' => 'required|string',
            'billing_address.postcode' => 'required|string',
            'billing_address.country' => 'required|string',
            'shipping_address' => 'nullable|array',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|integer',
            'items.*.item_type' => 'required|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'tax' => 'nullable|numeric|min:0',
            'delivery_fee' => 'nullable|numeric|min:0',
        ];

        // For update requests, include item ID validation
        if ($this->isMethod('put')) {
            $rules['items.*.id'] = 'nullable|exists:order_items,id';
        }

        return $rules;
    }
}