<?php

namespace Modules\Payments\Database\Seeders;

use Modules\EmailNotification\Support\DawatEmail;
use Modules\Payments\Notifications\CodPaymentReceivedNotification;

class CashPaymentNotificationSeeder extends PaymentNotificationSeeder
{
    protected function definitions(): array
    {
        return [
            'cod_payment_received' => [
                'name' => 'COD Payment Received',
                'description' => 'Triggered after cash collection marks a COD order as paid',
                'notification_class' => CodPaymentReceivedNotification::class,
                'email_subject' => 'COD Payment Received - {{order_number}}',
                'email_content' => $this->receiptTemplate(),
                'push_subject' => 'COD Payment Received',
                'push_body' => 'Cash payment of {{payment_amount}} received for order {{order_number}}. Your payment status is Paid.',
                'variables' => ['order_number' => 'Order number', 'payment_amount' => 'Cash payment amount'],
            ],
        ];
    }

    protected function receiptTemplate(): string
    {
        return DawatEmail::render([
            'title' => 'COD payment received — Dawat',
            'preheader' => 'We received your cash payment of {{payment_amount}} for order {{order_number}}.',
            'badge' => 'COD PAYMENT RECEIVED', 'headline' => 'Cash received.<br>Thank you',
            'greeting' => 'Hello {{customer_name}},',
            'intro' => 'Your cash payment has been recorded. Your order payment status is now Paid.',
            'blocks' => [
                DawatEmail::details('Payment receipt', [
                    'Order number' => '{{order_number}}', 'Amount received' => '{{payment_amount}}',
                    'Payment method' => 'Cash on Delivery', 'Payment status' => 'Paid',
                    'Transaction number' => '{{transaction_number}}', 'Collected on' => '{{collected_at}}',
                    'Collection note' => '{{collection_note}}',
                ]),
            ],
            'buttons' => [DawatEmail::button('View your order', '{{order_link}}')],
            'notes' => 'Keep this confirmation as your cash payment receipt.',
            'footer' => 'You received this email because a cash payment was recorded for your Dawat order.',
        ]);
    }
}
