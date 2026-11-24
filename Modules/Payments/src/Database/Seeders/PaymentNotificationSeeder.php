<?php

namespace Modules\Payments\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Notifications\Models\NotificationEvent;
use Modules\Notifications\Models\NotificationChannel;
use Modules\Notifications\Models\NotificationMapping;
use Modules\EmailNotification\Models\NotificationTemplate as EmailTemplate;
use Modules\EmailNotification\Support\DawatEmail;
use Modules\WebPushNotification\Models\NotificationTemplate as WebPushTemplate;

class PaymentNotificationSeeder extends Seeder
{
    public function run(): void
    {
        \Illuminate\Support\Facades\DB::transaction(function () {
            $channels = [
                'email' => NotificationChannel::firstOrCreate(['name' => 'email'], [
                    'channel_class' => \Modules\EmailNotification\Channels\EmailChannel::class,
                    'status' => true, 'config' => [],
                ]),
                'webpush' => NotificationChannel::firstOrCreate(['name' => 'webpush'], [
                    'channel_class' => \Modules\WebPushNotification\Channels\WebPushChannel::class,
                    'status' => true, 'config' => [],
                ]),
            ];
            foreach ($this->definitions() as $code => $definition) {
                $event = NotificationEvent::updateOrCreate(['event_code' => $code], [
                    'event_name' => $definition['name'], 'event_description' => $definition['description'],
                    'notification_class' => $definition['notification_class'],
                ]);
                $email = EmailTemplate::updateOrCreate(['name' => $code, 'locale' => 'en'], [
                    'subject' => $definition['email_subject'], 'content' => $definition['email_content'], 'status' => true,
                ]);
                $push = WebPushTemplate::updateOrCreate(['name' => $code], [
                    'subject' => $definition['push_subject'], 'body' => $definition['push_body'],
                    'variables' => $definition['variables'], 'is_active' => true,
                ]);
                $notification = (new \ReflectionClass($definition['notification_class']))->newInstanceWithoutConstructor();
                $recipients = array_fill_keys(array_keys($notification->getAvailableRecipients()), false);
                $recipients['notify_customer'] = true;
                foreach (['email' => $email, 'webpush' => $push] as $channel => $template) {
                    NotificationMapping::updateOrCreate([
                        'event_id' => $event->id, 'channel_id' => $channels[$channel]->id,
                    ], [
                        'template_id' => (string) $template->id,
                        'recipients' => $recipients,
                    ]);
                }
            }
        });
        $this->command?->info('Payment email and web-push templates mapped to customer recipients.');
    }

    protected function definitions(): array
    {
        return [
            'payment_initiated' => [
                'name' => 'Payment Initiated', 'description' => 'Triggered when a payment is initiated',
                'notification_class' => \Modules\Payments\Notifications\PaymentInitiatedNotification::class,
                'email_subject' => 'Payment Processing - {{order_number}}',
                'email_content' => $this->getPaymentInitiatedEmailTemplate(),
                'push_subject' => 'Processing Payment',
                'push_body' => 'Your payment for order {{order_number}} is being processed.',
                'variables' => ['order_number' => 'Order number'],
            ],
            'payment_success' => [
                'name' => 'Payment Successful', 'description' => 'Triggered when payment is successfully processed',
                'notification_class' => \Modules\Payments\Notifications\PaymentSuccessNotification::class,
                'email_subject' => 'Payment Confirmed - {{order_number}}',
                'email_content' => $this->getPaymentSuccessEmailTemplate(),
                'push_subject' => 'Payment Successful',
                'push_body' => 'Payment of {{payment_amount}} for order {{order_number}} confirmed.',
                'variables' => ['order_number' => 'Order number', 'payment_amount' => 'Payment amount'],
            ],
            'payment_failed' => [
                'name' => 'Payment Failed', 'description' => 'Triggered when payment fails',
                'notification_class' => \Modules\Payments\Notifications\PaymentFailedNotification::class,
                'email_subject' => 'Payment Failed - {{order_number}}',
                'email_content' => $this->getPaymentFailedEmailTemplate(),
                'push_subject' => 'Payment Failed',
                'push_body' => 'Payment for order {{order_number}} failed. Please try again.',
                'variables' => ['order_number' => 'Order number'],
            ],
            'payment_refunded' => [
                'name' => 'Payment Refunded', 'description' => 'Triggered when payment is refunded',
                'notification_class' => \Modules\Payments\Notifications\PaymentRefundedNotification::class,
                'email_subject' => 'Refund Processed - {{order_number}}',
                'email_content' => $this->getPaymentRefundedEmailTemplate(),
                'push_subject' => 'Refund Processed',
                'push_body' => 'Refund of {{refund_amount}} for order {{order_number}} has been processed.',
                'variables' => ['order_number' => 'Order number', 'refund_amount' => 'Refund amount'],
            ],
        ];
    }

    private function getPaymentInitiatedEmailTemplate(): string
    {
        return DawatEmail::render([
            'title' => 'Payment processing — Dawat',
            'preheader' => 'Your payment of {{payment_amount}} for order {{order_number}} is being processed.',
            'badge' => 'PAYMENT PROCESSING',
            'headline' => 'Your payment<br>is processing',
            'greeting' => 'Hello {{customer_name}},',
            'intro' => 'Your payment is being processed. Please do not close the page or refresh your browser.',
            'blocks' => [
                DawatEmail::details('Payment details', [
                    'Order number' => '{{order_number}}',
                    'Payment amount' => '{{payment_amount}}',
                    'Payment method' => '{{payment_method}}',
                    'Status' => 'Processing',
                ]),
            ],
            'buttons' => [DawatEmail::button('View order', '{{order_link}}')],
            'notes' => 'You will receive a confirmation email once your payment is processed.',
            'footer' => 'You received this email because a payment was started for your Dawat order.',
        ]);
    }

    private function getPaymentSuccessEmailTemplate(): string
    {
        return DawatEmail::render([
            'title' => 'Payment confirmed — Dawat',
            'preheader' => 'Your payment of {{payment_amount}} for order {{order_number}} was successful.',
            'badge' => 'PAYMENT CONFIRMED',
            'headline' => 'Payment received.<br>Order confirmed',
            'greeting' => 'Hello {{customer_name}},',
            'intro' => 'Your payment has been processed successfully and your order is now confirmed.',
            'blocks' => [
                DawatEmail::details('Payment confirmation', [
                    'Order number' => '{{order_number}}',
                    'Payment amount' => '{{payment_amount}}',
                    'Payment method' => '{{payment_method}}',
                    'Transaction ID' => '{{transaction_id}}',
                    'Status' => 'Confirmed',
                ]),
                DawatEmail::callout("What's next?", 'Your order has been sent to the restaurant. You will receive updates as it is prepared and delivered.'),
            ],
            'buttons' => [DawatEmail::button('Track order', '{{order_link}}')],
            'footer' => 'You received this email because you paid for an order on Dawat.',
        ]);
    }

    private function getPaymentFailedEmailTemplate(): string
    {
        return DawatEmail::render([
            'title' => 'Payment failed — Dawat',
            'preheader' => 'We could not process your payment for order {{order_number}}. Reason: {{failure_reason}}.',
            'badge' => 'PAYMENT FAILED',
            'headline' => "Your payment<br>didn't go through",
            'greeting' => 'Hello {{customer_name}},',
            'intro' => 'Unfortunately, your payment could not be processed. Please try again or use a different payment method.',
            'blocks' => [
                DawatEmail::details('Payment details', [
                    'Order number' => '{{order_number}}',
                    'Payment amount' => '{{payment_amount}}',
                    'Failure reason' => '{{failure_reason}}',
                    'Status' => 'Failed',
                ]),
                DawatEmail::list('What you can do', [
                    'Check your payment method details',
                    'Make sure sufficient funds are available',
                    'Try a different payment method',
                    'Contact your bank if the issue persists',
                ]),
            ],
            'buttons' => [DawatEmail::button('Retry payment', '{{retry_link}}')],
            'footer' => 'You received this email because a payment attempt for your Dawat order failed.',
        ]);
    }

    private function getPaymentRefundedEmailTemplate(): string
    {
        return DawatEmail::render([
            'title' => 'Refund processed — Dawat',
            'preheader' => 'Your refund of {{refund_amount}} for order {{order_number}} has been processed.',
            'badge' => 'REFUND PROCESSED',
            'headline' => 'Your refund<br>is on its way',
            'greeting' => 'Hello {{customer_name}},',
            'intro' => 'Your refund has been processed and will appear in your account within 3–5 business days.',
            'blocks' => [
                DawatEmail::details('Refund details', [
                    'Order number' => '{{order_number}}',
                    'Refund amount' => '{{refund_amount}}',
                    'Refund reason' => '{{refund_reason}}',
                    'Transaction ID' => '{{transaction_id}}',
                    'Status' => 'Processed',
                ]),
            ],
            'buttons' => [DawatEmail::button('Contact support', '{{support_link}}')],
            'notes' => 'If you have any questions about this refund, our support team is happy to help.',
            'footer' => 'You received this email because a refund was issued for your Dawat order.',
        ]);
    }
}
