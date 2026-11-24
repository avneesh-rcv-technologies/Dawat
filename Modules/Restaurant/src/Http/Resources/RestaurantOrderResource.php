<?php

namespace Modules\Restaurant\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Orders\Enums\OrderStatus;
use Modules\Orders\Services\OrderPresentationService;
use Modules\FoodManagements\Models\FoodItem;
use Modules\Restaurant\Models\Restaurant;
use Modules\Restaurant\Services\RestaurantOrderService;

class RestaurantOrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $order = $this->resource;
        $presentation = app(OrderPresentationService::class);
        $service = app(RestaurantOrderService::class);
        $statuses = $service->statuses($order->order_type, $order);
        $deleteError = $service->deletionError($order);
        $isCod = $service->isCod($order);
        $cashPayment = $order->payments->first(fn ($payment) => ($payment->details['collection_type'] ?? null) === 'cash');

        return [
            'id' => $order->id, 'order_number' => $order->order_number,
            'status' => $order->status, 'status_name' => OrderStatus::fromCode($order->status)?->label(),
            'order_type' => $order->order_type, 'order_type_label' => $order->isTakeaway() ? 'Pickup' : $order->formatted_order_type,
            'currency' => $order->currency,
            'customer' => $order->customer ? $order->customer->only(['id', 'name', 'email', 'phone']) : null,
            'restaurant' => $order->vendor ? [
                ...$order->vendor->only(['id', 'name', 'email', 'phone', 'address_line1', 'address_line2', 'city', 'state', 'zip_code']),
                'addresses' => $order->vendor instanceof Restaurant ? $order->vendor->addresses->map(fn ($address) => [
                    ...$address->only(['id', 'address_type', 'address_line_1', 'address_line_2', 'zip_code',
                        'city_id', 'state_id', 'country_id', 'latitude', 'longitude']),
                    'city_name' => $address->city?->name ?? $address->city_name,
                    'state_name' => $address->state?->name, 'country_name' => $address->country?->name,
                ])->values()->all() : [],
            ] : null,
            'delivery_address' => $order->delivery_address, 'billing_address' => $order->billing_address,
            'phone_number' => $order->phone_number, 'delivery_instructions' => $order->delivery_instructions,
            'notes' => $order->notes, 'coupon_code' => $order->coupon_code,
            'items_count' => $order->items->count(), 'quantity_total' => (int) $order->items->sum('quantity'),
            'items' => $order->items->map(function ($item) use ($presentation) {
                $snapshot = $item->selected_option_snapshot ?? [];
                $perPerson = ($snapshot['pricing_basis'] ?? null) === 'per_person';
                $itemData = $presentation->item($item);

                return [
                    ...$itemData, 'sku' => $item->sku,
                    'image' => $snapshot['image'] ?? ($item->item instanceof FoodItem ? ($item->item->image['url'] ?? null) : null),
                    'categories' => $item->item instanceof FoodItem ? $item->item->categories->map(fn ($category) => [
                        'id' => $category->id, 'name' => $category->name, 'parent_name' => $category->parent?->name,
                    ])->values()->all() : [],
                    'serving_summary' => [
                        'quantity_per_option' => $snapshot['quantity'] ?? null,
                        'unit' => $snapshot['unit'] ?? null, 'serves_per_option' => $snapshot['serves'] ?? null,
                        'quantity_ordered' => (int) $item->quantity,
                        'total_serves' => $perPerson ? $item->guest_count
                            : (isset($snapshot['serves']) ? (int) $snapshot['serves'] * (int) $item->quantity : null),
                        'pricing_basis' => $snapshot['pricing_basis'] ?? null,
                    ],
                    'price_breakdown' => [
                        'unit_price' => $item->price, 'subtotal' => $item->total_price,
                        'discount' => $item->discount_amount ?? '0.00',
                        'tax_rate' => $item->tax_rate, 'tax' => number_format($item->getTaxAmount(), 2, '.', ''),
                        'total' => $itemData['final_price'],
                        'delivery_fee' => $item->delivery_fee, 'is_tax_inclusive' => $item->is_tax_inclusive,
                    ],
                ];
            })->values()->all(),
            'price_breakdown' => $presentation->pricing($order),
            'fulfilment' => [
                ...$presentation->metadata($order),
                'actual_delivery_time' => $order->actual_delivery_time?->toIso8601String(),
                'cancelled_at' => $order->cancelled_at?->toIso8601String(),
                'cancellation_reason' => $order->cancellation_reason,
            ],
            'payment' => [
                'method' => $order->payment_method,
                'method_name' => $order->paymentConfiguration?->merchant_name ?: $order->paymentConfiguration?->method?->name,
                'status' => $order->payment_status, 'is_cod' => $isCod,
                'cash_collection' => $cashPayment ? [
                    'amount' => $cashPayment->amount, 'currency' => $cashPayment->currency,
                    'collected_at' => $cashPayment->details['collected_at'] ?? null,
                    'collected_by_name' => $cashPayment->details['collected_by_name'] ?? null,
                    'notes' => $cashPayment->details['collection_notes'] ?? null,
                ] : null,
                'records' => $order->payments->map->only([
                    'id', 'transaction_id', 'payment_type', 'amount', 'currency', 'status',
                    'gateway_reference', 'failure_reason', 'processed_at', 'created_at',
                ])->values()->all(),
            ],
            'refunds' => $order->refunds->map->only([
                'id', 'refund_number', 'refund_amount', 'refund_status', 'refund_source',
                'stripe_refund_id', 'transaction_id', 'created_at', 'processed_at',
            ])->values()->all(),
            'transactions' => $order->transactions->map->only([
                'id', 'transaction_number', 'payment_id', 'type', 'amount', 'currency', 'status',
                'gateway_reference', 'notes', 'processed_at', 'created_at',
            ])->values()->all(),
            'available_statuses' => array_values(array_filter($statuses, fn ($status) => $status['is_current'] || $status['can_transition'])),
            'actions' => [
                'can_update_status' => collect($statuses)->contains('can_transition', true),
                'can_delete' => $deleteError === null, 'delete_restriction_reason' => $deleteError,
                'can_collect_cash' => $isCod && in_array($order->payment_status, ['pending', 'unpaid'], true)
                    && ! in_array($order->status, ['C', 'F', 'R'], true),
                'refund_review_required' => in_array($order->status, ['C', 'F'], true)
                    && in_array($order->payment_status, ['paid', 'completed', 'captured'], true),
            ],
            'created_at' => $order->created_at?->toIso8601String(), 'updated_at' => $order->updated_at?->toIso8601String(),
        ];
    }
}
