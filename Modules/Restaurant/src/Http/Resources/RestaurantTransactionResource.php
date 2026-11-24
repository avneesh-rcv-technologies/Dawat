<?php

namespace Modules\Restaurant\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Orders\Enums\OrderStatus;
use Modules\Orders\Enums\TransactionStatus;
use Modules\Orders\Enums\TransactionType;

class RestaurantTransactionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $transaction = $this->resource;
        $order = $transaction->order;
        // Never expose a payment incorrectly linked to a different order.
        $payment = $transaction->payment && (int) $transaction->payment->order_id === (int) $transaction->order_id
            ? $transaction->payment : null;
        $method = $payment?->method ?? $order?->paymentConfiguration;
        $metadata = $transaction->metadata ?? [];

        return [
            'id' => $transaction->id, 'transaction_number' => $transaction->transaction_number,
            'type' => $transaction->type, 'type_label' => TransactionType::tryFrom($transaction->type)?->label() ?? ucfirst($transaction->type),
            'status' => $transaction->status, 'status_label' => TransactionStatus::tryFrom($transaction->status)?->label() ?? ucfirst($transaction->status),
            'amount' => $transaction->amount, 'currency' => $transaction->currency,
            'amount_formatted' => $transaction->currency.' '.number_format((float) $transaction->amount, 2),
            'gateway_reference' => $transaction->gateway_reference,
            'notes' => $payment || ! $transaction->payment_id ? $transaction->display_notes : $transaction->notes,
            'processed_at' => $transaction->processed_at?->toIso8601String(),
            'created_at' => $transaction->created_at?->toIso8601String(),
            'updated_at' => $transaction->updated_at?->toIso8601String(),
            'order' => $order ? [
                'id' => $order->id, 'order_number' => $order->order_number,
                'status' => $order->status, 'status_name' => OrderStatus::fromCode($order->status)?->label(),
                'order_type' => $order->order_type, 'payment_status' => $order->payment_status,
                'total' => $order->total, 'currency' => $order->currency,
            ] : null,
            'customer' => $order?->customer?->only(['id', 'name', 'email', 'phone']),
            'restaurant' => $order?->vendor?->only(['id', 'name', 'email', 'phone']),
            'payment' => $payment ? $payment->only([
                'id', 'payment_method_id', 'status', 'payment_type', 'amount', 'currency', 'failure_reason', 'processed_at',
            ]) : null,
            'payment_method' => $method ? [
                'id' => $method->id, 'name' => $method->merchant_name ?: $method->method?->name,
                'code' => $method->method?->code,
            ] : null,
            'cash_collection' => ($metadata['collection_type'] ?? null) === 'cash' ? [
                'collected_by' => $metadata['collected_by'] ?? $transaction->processed_by,
                'collected_by_name' => $metadata['collected_by_name'] ?? $transaction->collector?->name,
                'collected_at' => $metadata['collected_at'] ?? null,
                'notes' => $metadata['collection_notes'] ?? null,
            ] : null,
        ];
    }
}
