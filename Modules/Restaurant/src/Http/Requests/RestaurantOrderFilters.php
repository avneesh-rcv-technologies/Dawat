<?php

namespace Modules\Restaurant\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Orders\Enums\OrderStatus;
use Modules\Payments\Enums\PaymentStatus;

class RestaurantOrderFilters extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if (is_string($this->input('currency'))) {
            $this->merge(['currency' => strtoupper($this->input('currency'))]);
        }
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'search' => ['sometimes', 'string', 'max:255'],
            'status' => ['sometimes', Rule::in(array_map(fn ($status) => $status->code(), OrderStatus::cases()))],
            'payment_status' => ['sometimes', Rule::in(array_column(PaymentStatus::cases(), 'value'))],
            'transaction_status' => ['sometimes', Rule::in(['pending', 'completed', 'failed', 'reversed', 'declined', 'voided'])],
            'payment_method' => ['sometimes', 'string', 'max:100'],
            'order_type' => ['sometimes', Rule::in(['delivery', 'takeaway', 'pickup', 'dine_in'])],
            'customer_id' => ['sometimes', 'integer', 'min:1'],
            'currency' => ['sometimes', 'string', 'size:3'],
            'date_from' => ['sometimes', 'date_format:Y-m-d'],
            'date_to' => ['sometimes', 'date_format:Y-m-d', ...($this->filled('date_from') ? ['after_or_equal:date_from'] : [])],
            'min_total' => ['sometimes', 'numeric', 'min:0'],
            'max_total' => ['sometimes', 'numeric', 'min:0', ...($this->filled('min_total') ? ['gte:min_total'] : [])],
            'sort_by' => ['sometimes', Rule::in(['id', 'created_at', 'updated_at', 'total', 'order_number', 'status'])],
            'sort_dir' => ['sometimes', Rule::in(['asc', 'desc'])],
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ];
    }
}
