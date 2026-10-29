<?php

namespace Modules\Payments\Services;

use Modules\Orders\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Modules\Orders\Enums\OrderStatus;
use Modules\Payments\Models\Payments;
use Modules\Cart\Services\CartService;
use Modules\Orders\Enums\TransactionType;
use Modules\Payments\DTO\PaymentResponse;
use Modules\Payments\Enums\PaymentStatus;
use Modules\Orders\Enums\TransactionStatus;
use Modules\Orders\Services\TransactionService;
use Modules\Activitylog\Services\ActivityLoggerService;
use Modules\Notifications\Services\NotificationService;

class PaymentService
{
    protected TransactionService $transactionService;
    protected CartService $cartService;
    protected $notificationService;

    public function __construct(TransactionService $transactionService, CartService $cartService, NotificationService $notificationService)
    {
        $this->transactionService = $transactionService;
        $this->cartService = $cartService;
        $this->notificationService = $notificationService;
    }

    public function handlePaymentResponse(
        PaymentResponse $response,
        int $paymentMethodId,
        array $sessionData = []
    ): Payments {
        return DB::transaction(function () use ($response, $paymentMethodId, $sessionData) {

            $status = $response->getNormalizedStatus();

            \Log::info("Handling payment response with status: {$status->value} for order ID: {$sessionData['order_id']}");
            $order = Order::findOrFail($sessionData['order_id']);

            // Trigger payment initiated notification
            \Log::info("Triggering payment initiated notification for order ID: {$sessionData['order_id']}");

            // $this->notificationService->trigger(
            //     'Payments',
            //     'PaymentInitiated',
            //     $order->customer->email,
            //     null,
            //     ['order' => $order]
            // );

            $updateData = [
                'order_id'         => $sessionData['order_id'],
                'total'            => $sessionData['total'],
                'subtotal'         => $sessionData['subtotal'],
                'billing_address'  => $sessionData['billing_address'],
                'shipping_address' => $sessionData['shipping_address'],
                'notes'            => $sessionData['order_note'],
                'items'            => $sessionData['order_items'],
                'coupon_code'      => $sessionData['coupon_code'] ?? '',
                'discount'         => $sessionData['discount'] ?? 0,
                'tax'              => $sessionData['tax'] ?? 0,
                'payment_method'   => $paymentMethodId,
                'user_id'          => $sessionData['customer_details']['id'],
            ];

            if ($status === PaymentStatus::COMPLETED || $status === PaymentStatus::CAPTURED || $status === PaymentStatus::PAID) {
                $updateData['status'] = OrderStatus::DELIVERED->value;
                $updateData['payment_status'] = PaymentStatus::PAID->value;
            } elseif ($status === PaymentStatus::AUTHORIZED) {
                $updateData['status'] = OrderStatus::CONFIRMED->value;
                $updateData['payment_status'] = PaymentStatus::AUTHORIZED->value;
            } elseif ($status === PaymentStatus::REFUNDED) {
                $updateData['status'] = OrderStatus::CANCELLED->value;
                $updateData['payment_status'] = PaymentStatus::REFUNDED->value;
            } elseif ($status === PaymentStatus::UNPAID) {
                $updateData['status'] = OrderStatus::PENDING->value;
                $updateData['payment_status'] = PaymentStatus::UNPAID->value;
            } elseif ($status === PaymentStatus::PROCESSING) {
                $updateData['status'] = OrderStatus::CONFIRMED->value;
                $updateData['payment_status'] = PaymentStatus::PROCESSING->value;
            } elseif ($status === PaymentStatus::PENDING) {
                $updateData['payment_status'] = PaymentStatus::PENDING->value;
            } else {
                $updateData['status'] = OrderStatus::CANCELLED->value;
                $updateData['payment_status'] = PaymentStatus::FAILED->value;
            }

            if (in_array($status, [PaymentStatus::FAILED, PaymentStatus::CANCELLED, PaymentStatus::REFUNDED], true)) {
                app(\Modules\Orders\Services\OrderService::class)
                    ->markOrderPaymentFailed($order, 'Payment failed via ' . $response->gateway);
            } else {
                app(\Modules\Orders\Services\OrderService::class)
                    // The order items were already created and inventory reserved
                    // during checkout. A payment status update must not recreate
                    // them or decrement inventory a second time.
                    ->updateOrder($order, $updateData, false);
            }

            // Refresh order after update
            $order = Order::findOrFail($sessionData['order_id']);
        \Log::info("status value: " . $status->value . " for order ID: {$sessionData['order_id']} after order update");
            $payment = Payments::updateOrCreate(
                [
                    'order_id' => $order->id,
                    // 'transaction_id' => $response->gatewayPaymentId,
                ],
                [
                    'vendor_type' => $order->vendor_type,
                    'vendor_id' => $order->vendor_id,
                    'gateway_reference' => $response->gatewayPaymentId,
                    'payment_method_id' => $paymentMethodId,
                    'amount' => $response->amount,
                    'currency' => $response->currency,
                    'status' => $status->value,
                    'details' => [
                        'status'    => $response->status,
                        'card' => $response->card,
                        'billing_address' => $response->billingAddress,
                        'shipping_address' => $response->shippingAddress,
                        'metadata' => $response->metadata,
                    ],
                    'ip_address'         => request()->ip() ?? '',
                    'user_agent'         => request()->userAgent() ?? '',
                ]
            );

            // Trigger payment failed notification if payment failed
            if ($response->isFailure()) {
                $this->notificationService->trigger(
                    'Payments',
                    'PaymentFailed',
                    $order->customer->email,
                    $payment
                );
            }

            $transaction = $this->transactionService->record(
                order: $order,
                type: TransactionType::PAYMENT->value,
                amount: $payment->amount,
                status: match ($status) {
                    PaymentStatus::COMPLETED,
                    PaymentStatus::CAPTURED   => TransactionStatus::COMPLETED->value,
                    PaymentStatus::PAID       => TransactionStatus::COMPLETED->value,
                    PaymentStatus::AUTHORIZED => TransactionStatus::PENDING->value,
                    PaymentStatus::PENDING => TransactionStatus::PENDING->value,
                    PaymentStatus::PROCESSING => TransactionStatus::PENDING->value,
                    PaymentStatus::UNPAID     => TransactionStatus::PENDING->value,
                    PaymentStatus::REFUNDED   => TransactionStatus::VOIDED->value,
                    default                   => TransactionStatus::FAILED->value,
                },
                payment: $payment,
                metadata: [
                    'transaction_id' => $response->gatewayPaymentId,
                    'gateway_response' => $response->gatewayPaymentId,
                ],
                notes: $status->transactionNote($response->gateway)
            );

            $payment->transaction_id = $transaction->id;
            $payment->save();

            // $this->cartService->clear();

            $res = $payment->fresh();

            if ($response->isSuccessful()) {
                ActivityLoggerService::log(
                    "Payment:: Successful for order #{$order['order_id']}",
                    Auth::guard('customer')->user(),
                    [
                        'user_id'    => Auth::guard('customer')->id(),
                        'order_id'   => $order['order_id'],
                        'severity'   => 'success',
                        'event'      => 'Payment Success',
                    ],
                    'customer'
                );

                $user = Auth::guard('customer')->user();

                // Trigger payment success notification
                $this->notificationService->trigger(
                    'Payments',
                    'PaymentSuccess',
                    $user->email,
                    $payment
                );

                $this->notificationService->trigger(
                    'Cart', //module name
                    'PaymentConfirmation', // notification class name before Notification
                    Auth::guard('customer')->user()->email,
                    ['payment' => $res, 'customer' => Auth::guard('customer')->user(), 'order' => $order]
                );

                $this->notificationService->trigger(
                    'Customers', //module name
                    'QuestionnaireSend', // notification class
                    $user,
                    [
                        'customer' => $user,
                        'order' => $order,
                    ]
                );

                $this->notificationService->trigger(
                    'ScoreCard', //module name
                    'ScorecardReminder', // notification class
                    $user,
                    [
                        'customer' => $user,
                        'order' => $order,
                    ]
                );
            }
            return $res;
        });
    }
}
