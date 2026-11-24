<?php

namespace Modules\Payments\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Notifications\Services\NotificationService;
use Modules\Notifications\Models\NotificationEvent;
use Modules\Orders\Services\CodPaymentService;
use Modules\Payments\Models\Payments;

class PaymentSuccessNotificationService
{
    public const QUEUED_AT = 'payment_success_notification_queued_at';
    public const CHANNELS = 'payment_success_notification_channels';

    public function __construct(private NotificationService $notifications) {}

    public function schedule(Payments $payment): void
    {
        if (! $payment->exists || ! in_array($payment->status, ['completed', 'captured', 'paid'], true)
            || ! in_array($payment->payment_type, [null, 'order_payment'], true)) {
            return;
        }

        DB::afterCommit(function () use ($payment) {
            try {
                $this->send($payment->id);
            } catch (\Throwable $exception) {
                // Receipt/configuration/queue outages must not change a committed payment.
                Log::warning('Payment success notification failed after commit.', [
                    'payment_id' => $payment->id, 'exception_class' => get_class($exception),
                ]);
            }
        });
    }

    public function send(int $paymentId): void
    {
        DB::transaction(function () use ($paymentId) {
            // Serialize simultaneous browser-return/webhook callbacks and queue once.
            $payment = Payments::whereKey($paymentId)->lockForUpdate()->first();
            if (! $payment || ! in_array($payment->status, ['completed', 'captured', 'paid'], true)
                || ! in_array($payment->payment_type, [null, 'order_payment'], true)
                || ! empty($payment->details[self::QUEUED_AT])) {
                return;
            }
            $payment->load(['order.customer', 'method.method', 'transactions']);
            $order = $payment->order;
            if (! $order || ! in_array($order->payment_status, ['paid', 'completed', 'captured'], true)) {
                return;
            }
            // COD collection has its own audited receipt and notification event.
            if (($payment->details['collection_type'] ?? null) === 'cash'
                || app(CodPaymentService::class)->isCodConfiguration($payment->method)) {
                return;
            }
            if (! $order->customer?->email) {
                Log::warning('Payment receipt has no customer email.', ['payment_id' => $paymentId, 'order_id' => $order->id]);
                return;
            }

            $event = NotificationEvent::where('event_code', 'payment_success')->first();
            $channels = $event?->activeMappings()->with('channel')->get()->pluck('channel.name')->filter()->unique()->values();
            if (! $channels || $channels->isEmpty()) {
                Log::warning('Payment success notification has no active mappings. Run PaymentNotificationSeeder.', ['payment_id' => $paymentId]);
                return;
            }
            $details = $payment->details ?? [];
            $queued = is_array($details[self::CHANNELS] ?? null) ? $details[self::CHANNELS] : [];
            foreach ($channels as $channel) {
                if (! empty($queued[$channel])) {
                    continue;
                }
                if ($this->notifications->trigger('Payments', 'PaymentSuccess', $order->customer, $payment, [$channel])) {
                    $queued[$channel] = now()->toIso8601String();
                    Log::info('Payment success notification queued.', ['payment_id' => $paymentId, 'order_id' => $order->id, 'channel' => $channel]);
                } else {
                    Log::warning('Payment success notification channel was not queued.', ['payment_id' => $paymentId, 'channel' => $channel]);
                }
            }
            $details[self::CHANNELS] = $queued;
            if ($channels->every(fn ($channel) => ! empty($queued[$channel]))) {
                $details[self::QUEUED_AT] = now()->toIso8601String();
            }
            $payment->forceFill(['details' => $details])->saveQuietly();
        });
    }
}
