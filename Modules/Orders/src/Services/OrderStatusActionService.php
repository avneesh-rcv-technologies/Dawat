<?php

namespace Modules\Orders\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Modules\Acl\Models\Admin;
use Modules\Orders\Models\Order;
use Modules\Payments\Models\Refund;
use Modules\Payments\Services\RefundService;

/** Confirm destructive status changes and record manual refunds through the refund ledger. */
class OrderStatusActionService
{
    public static function rules(string $status): array
    {
        return [
            'confirmed' => in_array($status, ['C', 'R'], true) ? ['required', 'accepted'] : ['sometimes', 'boolean'],
            'cancellation_reason' => ['nullable', 'string', 'max:2000'],
            'stripe_refund_id' => ['nullable', 'string', 'max:255', Rule::requiredIf($status === 'R')],
            'admin_notes' => ['nullable', 'string', 'max:5000'],
        ];
    }

    public function update(Order $order, string $status, array $data, Admin $actor, string $source): Order
    {
        if (in_array($status, ['C', 'R'], true) && ! in_array($data['confirmed'] ?? null, [true, 1, '1', 'yes', 'on', 'true'], true)) {
            throw ValidationException::withMessages(['confirmed' => 'Confirm the cancellation or manual refund before continuing.']);
        }

        if ($status === 'R') {
            abort_unless(app(\Modules\Payments\Services\RefundAccess::class)->canUpdate($actor), 403,
                'Only authorized platform admins can record completed refunds. Cancel the order to request a refund for admin review.');
        }
        return DB::transaction(function () use ($order, $status, $data, $actor, $source) {
            $order = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();
            $orders = app(OrderService::class);
            if ($status !== 'R') {
                $order = $orders->updateOrderStatus($order, $status, $data['cancellation_reason'] ?? null, $source, $actor);
                if ($status === 'C') {
                    app(RefundService::class)->requestForCancellation($order, $source, $actor);
                }
                return $order;
            }

            $reference = trim((string) ($data['stripe_refund_id'] ?? ''));
            if ($reference === '') {
                throw ValidationException::withMessages(['stripe_refund_id' => 'Enter the reference of the refund already completed through your payment provider.']);
            }
            // Cancelling and recording the refund is atomic. Never turn the cancellation into R.
            if ($order->status !== 'C') {
                $order = $orders->updateOrderStatus($order, 'C', $data['cancellation_reason'] ?? null, $source, $actor);
            } else {
                app(RefundService::class)->requestForCancellation($order, $source, $actor);
            }
            $refunds = Refund::where('order_id', $order->id)->orderBy('id')->lockForUpdate()->get();
            $open = $refunds->where('refund_status', '!=', 'succeeded');
            if ($open->isEmpty() && $order->payment_status === 'refunded'
                && $refunds->contains(fn ($refund) => $refund->stripe_refund_id === $reference)) {
                return $order; // Retry the same completed manual refund without duplicate entries.
            }
            if ($open->count() !== 1) {
                throw ValidationException::withMessages(['status' => $open->isEmpty()
                    ? 'There is no captured payment remaining to refund.'
                    : 'This order has multiple payments. Record each refund separately from Refund requests.']);
            }
            app(RefundService::class)->update($open->first(), [
                'refund_status' => 'succeeded', 'stripe_refund_id' => $reference,
                'admin_notes' => $data['admin_notes'] ?? $open->first()->admin_notes,
            ], $actor);

            return $order->refresh();
        });
    }
}
