<?php

namespace Modules\Restaurant\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Orders\Enums\OrderStatus;
use Modules\Orders\Enums\TransactionStatus;
use Modules\Orders\Enums\TransactionType;
use Modules\Payments\Enums\PaymentStatus;

class RestaurantTransactionFilters extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (is_string($this->input('currency'))) {
            $this->merge(['currency' => strtoupper($this->input('currency'))]);
        }
    }

    public function rules(): array
    {
        return [
            'search' => ['sometimes', 'string', 'max:255'],
            'type' => ['sometimes', Rule::in(array_column(TransactionType::cases(), 'value'))],
            'status' => ['sometimes', Rule::in([...array_column(TransactionStatus::cases(), 'value'), 'reversed'])],
            'order_id' => ['sometimes', 'integer', 'min:1'],
            'payment_id' => ['sometimes', 'integer', 'min:1'],
            'customer_id' => ['sometimes', 'integer', 'min:1'],
            'order_status' => ['sometimes', Rule::in(array_map(fn ($status) => $status->code(), OrderStatus::cases()))],
            'payment_status' => ['sometimes', Rule::in(array_column(PaymentStatus::cases(), 'value'))],
            'payment_method' => ['sometimes', 'string', 'max:100'],
            'order_type' => ['sometimes', Rule::in(['delivery', 'pickup', 'takeaway', 'dine_in'])],
            'currency' => ['sometimes', 'string', 'size:3'],
            'date_from' => ['sometimes', 'date_format:Y-m-d'],
            'date_to' => ['sometimes', 'date_format:Y-m-d', ...($this->filled('date_from') ? ['after_or_equal:date_from'] : [])],
            'processed_from' => ['sometimes', 'date_format:Y-m-d'],
            'processed_to' => ['sometimes', 'date_format:Y-m-d', ...($this->filled('processed_from') ? ['after_or_equal:processed_from'] : [])],
            'min_amount' => ['sometimes', 'numeric'],
            'max_amount' => ['sometimes', 'numeric', ...($this->filled('min_amount') ? ['gte:min_amount'] : [])],
            'sort_by' => ['sometimes', Rule::in(['id', 'transaction_number', 'created_at', 'processed_at', 'amount', 'status', 'type'])],
            'sort_dir' => ['sometimes', Rule::in(['asc', 'desc'])],
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ];
    }
}
