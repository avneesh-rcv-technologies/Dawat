<?php

namespace Modules\Discounts\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class StoreDiscountRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:200',
            'type' => ['required', Rule::in(['F', 'P'])],
            'amount' => 'required|numeric|min:0',
            'apply_to' => ['nullable', Rule::in(['subtotal', 'total'])],
            'is_active' => 'boolean',
            'starts_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after_or_equal:starts_at',

            'user_groups' => 'nullable|array',
            'user_groups.*' => 'integer|exists:user_groups,id',

            'coupons' => 'nullable|array',
            'coupons.*.code' => 'required|string|unique:coupons,code',
            'coupons.*.description' => 'nullable|string',
            'coupons.*.starts_at' => 'nullable|date',
            'coupons.*.expires_at' => 'nullable|date|after_or_equal:coupons.*.starts_at',
            'coupons.*.usage_limit' => 'nullable|integer|min:1',
            'coupons.*.usage_limit_per_user' => 'nullable|integer|min:1',
            'coupons.*.min_order_amount' => 'nullable|numeric|min:0',
            'coupons.*.is_active' => 'boolean',

            'rules' => 'nullable|array',
            'rules.*.rule_type' => ['required', Rule::in(['product', 'category', 'subtotal', 'quantity'])],
            'rules.*.rule_id' => [
                'nullable',
                'required_if:rules.*.rule_type,product',
                'required_if:rules.*.rule_type,category',
                'integer'
            ],
            'rules.*.condition_type' => ['required', Rule::in(['greater_than', 'less_than', 'equals', 'not_equals'])],
            'rules.*.rule_value' => 'required|string',
        ];
    }

    public function messages()
    {
        return [
            'coupons.*.code.unique' => 'The coupon code :input has already been taken.',
            'expires_at.after_or_equal' => 'The expiration date must be after or equal to the start date.',
            'coupons.*.expires_at.after_or_equal' => 'The coupon expiration date must be after or equal to the start date.',
            'rules.*.rule_id.required_if' => 'The target is required when the rule type is product or category.',
        ];
    }
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'errors' => $validator->errors()->first(),
            ])
        );
    }

}
