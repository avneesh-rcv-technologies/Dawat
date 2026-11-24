<?php

namespace Modules\Payments\Database\Seeders;

use Modules\EmailNotification\Support\DawatEmail;
use Modules\Payments\Notifications\RefundStatusUpdateNotification;

class RefundNotificationSeeder extends PaymentNotificationSeeder
{
    protected function definitions(): array
    {
        return [
            'refund_status_updated' => [
                'name' => 'Refund Status Updated',
                'description' => 'Sent after a refund request is created or changes status',
                'notification_class' => RefundStatusUpdateNotification::class,
                'email_subject' => 'Refund {{refund_status}} — {{order_number}}',
                'email_content' => DawatEmail::render([
                    'title' => 'Refund update — Dawat', 'preheader' => '{{status_message}}',
                    'badge' => 'REFUND UPDATE', 'headline' => 'Refund {{refund_status}}',
                    'greeting' => 'Hello {{customer_name}},', 'intro' => '{{status_message}}',
                    'blocks' => [DawatEmail::details('Refund details', [
                        'Order' => '{{order_number}}', 'Refund request' => '{{refund_number}}',
                        'Amount' => '{{refund_amount}}', 'Status' => '{{refund_status}}',
                        'Reason' => '{{refund_reason}}', 'Reference' => '{{refund_reference}}',
                    ])],
                    'footer' => 'You received this email because the refund status for your order changed.',
                ]),
                'push_subject' => 'Refund {{refund_status}}',
                'push_body' => '{{refund_amount}} for order {{order_number}}: {{status_message}}',
                'variables' => ['order_number' => 'Order number', 'refund_amount' => 'Refund amount',
                    'refund_status' => 'Refund status', 'status_message' => 'Status explanation'],
            ],
        ];
    }
}
