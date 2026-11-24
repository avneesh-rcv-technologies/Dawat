<?php

namespace Modules\Payments\Notifications;

use Modules\Payments\Models\Payments;

class CodPaymentReceivedNotification extends PaymentSuccessNotification
{
    public function getEventCode(): string
    {
        return 'cod_payment_received';
    }

    public function getVariables(): array
    {
        if (!$this->payment instanceof Payments) return [];
        $variables = parent::getVariables();
        $payment = $this->payment;
        $details = $payment->details ?? [];
        return array_merge($variables, [
            'payment_method' => 'Cash on Delivery',
            'payment_status' => 'Paid',
            'transaction_number' => $payment->transactions->firstWhere('id', (int) $payment->transaction_id)?->transaction_number
                ?? $payment->gateway_reference ?? 'Not recorded',
            'collected_at' => $payment->processed_at?->format('M d, Y h:i A') ?? 'Not recorded',
            'collection_note' => e($details['collection_notes'] ?? 'Not provided'),
            'support_link' => url('/support'),
        ]);
    }

    public function getAvailableVariables(): array
    {
        return array_merge(parent::getAvailableVariables(), [
            'payment_status' => 'Payment status (Paid)', 'transaction_number' => 'Cash receipt transaction number',
            'collected_at' => 'Cash collection date and time', 'collection_note' => 'Cash collection note',
            'support_link' => 'Support page link',
        ]);
    }
}
