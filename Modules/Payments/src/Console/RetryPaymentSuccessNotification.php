<?php

namespace Modules\Payments\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Modules\Orders\Models\Transaction;
use Modules\Payments\Models\Payments;
use Modules\Payments\Services\PaymentSuccessNotificationService;

class RetryPaymentSuccessNotification extends Command
{
    protected $signature = 'payments:retry-success-notification {payment_id?} {--transaction-id=} {--dry-run}';
    protected $description = 'Queue missing success receipt channels for one completed payment, without charging again';

    public function handle(PaymentSuccessNotificationService $receipts): int
    {
        $paymentId = $this->argument('payment_id');
        $transactionId = $this->option('transaction-id');
        if (($paymentId === null) === ($transactionId === null)) {
            $this->error('Provide a payment ID or --transaction-id, not both.');
            return self::FAILURE;
        }
        $id = $paymentId ?? $transactionId;
        if (! ctype_digit((string) $id) || (int) $id < 1) {
            $this->error('Use a positive numeric ID.');
            return self::FAILURE;
        }
        try {
            if ($transactionId !== null) {
                $paymentId = Transaction::where('type', 'payment')->whereKey($transactionId)->value('payment_id');
            }
            $payment = $paymentId ? Payments::with('order')->find($paymentId) : null;
            if (! $payment || ! in_array($payment->status, ['completed', 'paid', 'captured'], true)
                || ! in_array($payment->payment_type, [null, 'order_payment'], true)
                || ! in_array($payment->order?->payment_status, ['paid', 'completed', 'captured'], true)) {
                $this->error('No eligible completed order payment found.');
                return self::FAILURE;
            }
            if ($this->option('dry-run')) {
                $this->info('Payment #'.$payment->id.' is completed. No receipt sent.');
                return self::SUCCESS;
            }
            $receipts->send($payment->id);
            if (empty($payment->refresh()->details[PaymentSuccessNotificationService::QUEUED_AT])) {
                $this->error('Some receipt channels were not queued. Check mappings, channel logs, and COD eligibility.');
                return self::FAILURE;
            }
            $this->info('Receipt channels queued or already queued for payment #'.$payment->id.'.');
            return self::SUCCESS;
        } catch (\Throwable $exception) {
            Log::warning('Payment receipt retry failed.', ['payment_id' => $paymentId, 'exception_class' => get_class($exception)]);
            $this->error('Receipt retry failed. Check the application log. Payment state was not changed.');
            return self::FAILURE;
        }
    }
}
