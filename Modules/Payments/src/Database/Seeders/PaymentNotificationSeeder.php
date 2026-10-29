<?php

namespace Modules\Payments\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Notifications\Models\NotificationEvent;
use Modules\Notifications\Models\NotificationChannel;
use Modules\Notifications\Models\NotificationMapping;
use Modules\EmailNotification\Models\NotificationTemplate as EmailTemplate;
use Modules\WebPushNotification\Models\NotificationTemplate as WebPushTemplate;

class PaymentNotificationSeeder extends Seeder
{
    public function run(): void
    {
        // Create Events
        $paymentInitiatedEvent = NotificationEvent::updateOrCreate(
            ['event_code' => 'payment_initiated'],
            [
                'event_name' => 'Payment Initiated',
                'event_description' => 'Triggered when a payment is initiated',
            ]
        );

        $paymentSuccessEvent = NotificationEvent::updateOrCreate(
            ['event_code' => 'payment_success'],
            [
                'event_name' => 'Payment Successful',
                'event_description' => 'Triggered when payment is successfully processed',
            ]
        );

        $paymentFailedEvent = NotificationEvent::updateOrCreate(
            ['event_code' => 'payment_failed'],
            [
                'event_name' => 'Payment Failed',
                'event_description' => 'Triggered when payment fails',
            ]
        );

        $paymentRefundedEvent = NotificationEvent::updateOrCreate(
            ['event_code' => 'payment_refunded'],
            [
                'event_name' => 'Payment Refunded',
                'event_description' => 'Triggered when payment is refunded',
            ]
        );

        // Create Email Templates
        $paymentInitiatedEmailTemplate = EmailTemplate::updateOrCreate(
            ['name' => 'payment_initiated'],
            [
                'subject' => 'Payment Processing - {{order_number}}',
                'content' => $this->getPaymentInitiatedEmailTemplate(),
                'status' => true,
                'locale' => 'en',
            ]
        );

        $paymentSuccessEmailTemplate = EmailTemplate::updateOrCreate(
            ['name' => 'payment_success'],
            [
                'subject' => 'Payment Confirmed - {{order_number}}',
                'content' => $this->getPaymentSuccessEmailTemplate(),
                'status' => true,
                'locale' => 'en',
            ]
        );

        $paymentFailedEmailTemplate = EmailTemplate::updateOrCreate(
            ['name' => 'payment_failed'],
            [
                'subject' => 'Payment Failed - {{order_number}}',
                'content' => $this->getPaymentFailedEmailTemplate(),
                'status' => true,
                'locale' => 'en',
            ]
        );

        $paymentRefundedEmailTemplate = EmailTemplate::updateOrCreate(
            ['name' => 'payment_refunded'],
            [
                'subject' => 'Refund Processed - {{order_number}}',
                'content' => $this->getPaymentRefundedEmailTemplate(),
                'status' => true,
                'locale' => 'en',
            ]
        );

        // Create WebPush Templates
        $paymentInitiatedPushTemplate = WebPushTemplate::updateOrCreate(
            ['name' => 'payment_initiated'],
            [
                'subject' => 'Processing Payment',
                'body' => 'Your payment for order {{order_number}} is being processed.',
                'variables' => json_encode([
                    'order_number' => 'Order number',
                ]),
                'is_active' => true,
            ]
        );

        $paymentSuccessPushTemplate = WebPushTemplate::updateOrCreate(
            ['name' => 'payment_success'],
            [
                'subject' => 'Payment Successful',
                'body' => 'Payment of {{payment_amount}} for order {{order_number}} confirmed.',
                'variables' => json_encode([
                    'order_number' => 'Order number',
                    'payment_amount' => 'Payment amount',
                ]),
                'is_active' => true,
            ]
        );

        $paymentFailedPushTemplate = WebPushTemplate::updateOrCreate(
            ['name' => 'payment_failed'],
            [
                'subject' => 'Payment Failed',
                'body' => 'Payment for order {{order_number}} failed. Please try again.',
                'variables' => json_encode([
                    'order_number' => 'Order number',
                ]),
                'is_active' => true,
            ]
        );

        $paymentRefundedPushTemplate = WebPushTemplate::updateOrCreate(
            ['name' => 'payment_refunded'],
            [
                'subject' => 'Refund Processed',
                'body' => 'Refund of {{refund_amount}} for order {{order_number}} has been processed.',
                'variables' => json_encode([
                    'order_number' => 'Order number',
                    'refund_amount' => 'Refund amount',
                ]),
                'is_active' => true,
            ]
        );

        // Get Channels
        $emailChannel = NotificationChannel::where('name', 'email')->first();
        $webpushChannel = NotificationChannel::where('name', 'webpush')->first();

        // Create Mappings for Payment Initiated
        if ($emailChannel) {
            NotificationMapping::updateOrCreate(
                [
                    'event_id' => $paymentInitiatedEvent->id,
                    'channel_id' => $emailChannel->id,
                ],
                [
                    'template_id' => (string)$paymentInitiatedEmailTemplate->id,
                    'priority' => 1,
                    'custom_config' => json_encode([
                        'notify_customer' => true,
                        'notify_admin' => true,
                    ]),
                ]
            );
        }

        if ($webpushChannel) {
            NotificationMapping::updateOrCreate(
                [
                    'event_id' => $paymentInitiatedEvent->id,
                    'channel_id' => $webpushChannel->id,
                ],
                [
                    'template_id' => (string)$paymentInitiatedPushTemplate->id,
                    'priority' => 2,
                    'custom_config' => json_encode([
                        'notify_customer' => true,
                    ]),
                ]
            );
        }

        // Create Mappings for Payment Success
        if ($emailChannel) {
            NotificationMapping::updateOrCreate(
                [
                    'event_id' => $paymentSuccessEvent->id,
                    'channel_id' => $emailChannel->id,
                ],
                [
                    'template_id' => (string)$paymentSuccessEmailTemplate->id,
                    'priority' => 1,
                    'custom_config' => json_encode([
                        'notify_customer' => true,
                        'notify_admin' => true,
                        'notify_restaurant' => true,
                    ]),
                ]
            );
        }

        if ($webpushChannel) {
            NotificationMapping::updateOrCreate(
                [
                    'event_id' => $paymentSuccessEvent->id,
                    'channel_id' => $webpushChannel->id,
                ],
                [
                    'template_id' => (string)$paymentSuccessPushTemplate->id,
                    'priority' => 2,
                    'custom_config' => json_encode([
                        'notify_customer' => true,
                    ]),
                ]
            );
        }

        // Create Mappings for Payment Failed
        if ($emailChannel) {
            NotificationMapping::updateOrCreate(
                [
                    'event_id' => $paymentFailedEvent->id,
                    'channel_id' => $emailChannel->id,
                ],
                [
                    'template_id' => (string)$paymentFailedEmailTemplate->id,
                    'priority' => 1,
                    'custom_config' => json_encode([
                        'notify_customer' => true,
                        'notify_admin' => true,
                    ]),
                ]
            );
        }

        if ($webpushChannel) {
            NotificationMapping::updateOrCreate(
                [
                    'event_id' => $paymentFailedEvent->id,
                    'channel_id' => $webpushChannel->id,
                ],
                [
                    'template_id' => (string)$paymentFailedPushTemplate->id,
                    'priority' => 2,
                    'custom_config' => json_encode([
                        'notify_customer' => true,
                    ]),
                ]
            );
        }

        // Create Mappings for Payment Refunded
        if ($emailChannel) {
            NotificationMapping::updateOrCreate(
                [
                    'event_id' => $paymentRefundedEvent->id,
                    'channel_id' => $emailChannel->id,
                ],
                [
                    'template_id' => (string)$paymentRefundedEmailTemplate->id,
                    'priority' => 1,
                    'custom_config' => json_encode([
                        'notify_customer' => true,
                        'notify_admin' => true,
                    ]),
                ]
            );
        }

        if ($webpushChannel) {
            NotificationMapping::updateOrCreate(
                [
                    'event_id' => $paymentRefundedEvent->id,
                    'channel_id' => $webpushChannel->id,
                ],
                [
                    'template_id' => (string)$paymentRefundedPushTemplate->id,
                    'priority' => 2,
                    'custom_config' => json_encode([
                        'notify_customer' => true,
                    ]),
                ]
            );
        }
    }

    private function getPaymentInitiatedEmailTemplate(): string
    {
        return <<<'HTML'
<html>
<body style="font-family: Arial, sans-serif; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h2 style="color: #007bff;">Payment Processing</h2>
        <p>Dear {{customer_name}},</p>
        <p>Your payment is being processed. Please do not close this page or refresh your browser.</p>
        
        <h3 style="color: #007bff; border-bottom: 2px solid #007bff; padding-bottom: 10px;">Payment Details</h3>
        <table style="width: 100%; margin: 20px 0;">
            <tr>
                <td style="padding: 8px;"><strong>Order Number:</strong></td>
                <td style="padding: 8px;">{{order_number}}</td>
            </tr>
            <tr style="background-color: #f9f9f9;">
                <td style="padding: 8px;"><strong>Payment Amount:</strong></td>
                <td style="padding: 8px; color: #ff9800; font-weight: bold;">{{payment_amount}}</td>
            </tr>
            <tr>
                <td style="padding: 8px;"><strong>Payment Method:</strong></td>
                <td style="padding: 8px;">{{payment_method}}</td>
            </tr>
            <tr style="background-color: #f9f9f9;">
                <td style="padding: 8px;"><strong>Status:</strong></td>
                <td style="padding: 8px; color: #ff9800; font-weight: bold;">Processing</td>
            </tr>
        </table>

        <p style="margin-top: 20px; color: #666;">
            You will receive a confirmation email once your payment is successfully processed.
        </p>

        <div style="margin-top: 30px; text-align: center;">
            <a href="{{order_link}}" style="background-color: #007bff; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;">View Order</a>
        </div>
        
        <p style="margin-top: 30px; color: #666; font-size: 12px;">
            Best regards,<br>
            {{app_name}} Team
        </p>
    </div>
</body>
</html>
HTML;
    }

    private function getPaymentSuccessEmailTemplate(): string
    {
        return <<<'HTML'
<html>
<body style="font-family: Arial, sans-serif; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h2 style="color: #28a745;">Payment Successful!</h2>
        <p>Dear {{customer_name}},</p>
        <p>Your payment has been successfully processed. Your order is now confirmed.</p>
        
        <h3 style="color: #007bff; border-bottom: 2px solid #007bff; padding-bottom: 10px;">Payment Confirmation</h3>
        <table style="width: 100%; margin: 20px 0; border-collapse: collapse;">
            <tr style="background-color: #f9f9f9;">
                <td style="padding: 12px; border-bottom: 1px solid #ddd;"><strong>Order Number:</strong></td>
                <td style="padding: 12px; border-bottom: 1px solid #ddd;">{{order_number}}</td>
            </tr>
            <tr>
                <td style="padding: 12px; border-bottom: 1px solid #ddd;"><strong>Payment Amount:</strong></td>
                <td style="padding: 12px; border-bottom: 1px solid #ddd; color: #28a745; font-weight: bold;">{{payment_amount}}</td>
            </tr>
            <tr style="background-color: #f9f9f9;">
                <td style="padding: 12px; border-bottom: 1px solid #ddd;"><strong>Payment Method:</strong></td>
                <td style="padding: 12px; border-bottom: 1px solid #ddd;">{{payment_method}}</td>
            </tr>
            <tr>
                <td style="padding: 12px; border-bottom: 1px solid #ddd;"><strong>Transaction ID:</strong></td>
                <td style="padding: 12px; border-bottom: 1px solid #ddd;">{{transaction_id}}</td>
            </tr>
            <tr style="background-color: #f9f9f9;">
                <td style="padding: 12px;"><strong>Status:</strong></td>
                <td style="padding: 12px; color: #28a745; font-weight: bold;">Confirmed</td>
            </tr>
        </table>

        <h3 style="color: #007bff; border-bottom: 2px solid #007bff; padding-bottom: 10px;">What's Next?</h3>
        <p>Your order has been sent to the restaurant. You will receive updates as your order is being prepared and delivered.</p>

        <div style="margin-top: 30px; text-align: center;">
            <a href="{{order_link}}" style="background-color: #28a745; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;">Track Order</a>
        </div>
        
        <p style="margin-top: 30px; color: #666; font-size: 12px;">
            Best regards,<br>
            {{app_name}} Team
        </p>
    </div>
</body>
</html>
HTML;
    }

    private function getPaymentFailedEmailTemplate(): string
    {
        return <<<'HTML'
<html>
<body style="font-family: Arial, sans-serif; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h2 style="color: #dc3545;">Payment Failed</h2>
        <p>Dear {{customer_name}},</p>
        <p>Unfortunately, your payment could not be processed. Please try again or use a different payment method.</p>
        
        <h3 style="color: #007bff; border-bottom: 2px solid #007bff; padding-bottom: 10px;">Payment Details</h3>
        <table style="width: 100%; margin: 20px 0; border-collapse: collapse;">
            <tr style="background-color: #f9f9f9;">
                <td style="padding: 12px; border-bottom: 1px solid #ddd;"><strong>Order Number:</strong></td>
                <td style="padding: 12px; border-bottom: 1px solid #ddd;">{{order_number}}</td>
            </tr>
            <tr>
                <td style="padding: 12px; border-bottom: 1px solid #ddd;"><strong>Payment Amount:</strong></td>
                <td style="padding: 12px; border-bottom: 1px solid #ddd;">{{payment_amount}}</td>
            </tr>
            <tr style="background-color: #f9f9f9;">
                <td style="padding: 12px; border-bottom: 1px solid #ddd;"><strong>Failure Reason:</strong></td>
                <td style="padding: 12px; border-bottom: 1px solid #ddd; color: #dc3545;">{{failure_reason}}</td>
            </tr>
            <tr>
                <td style="padding: 12px;"><strong>Status:</strong></td>
                <td style="padding: 12px; color: #dc3545; font-weight: bold;">Failed</td>
            </tr>
        </table>

        <h3 style="color: #007bff; border-bottom: 2px solid #007bff; padding-bottom: 10px;">What You Can Do</h3>
        <ul style="line-height: 1.8;">
            <li>Check your payment method details</li>
            <li>Ensure sufficient funds are available</li>
            <li>Try a different payment method</li>
            <li>Contact your bank if the issue persists</li>
        </ul>

        <div style="margin-top: 30px; text-align: center;">
            <a href="{{retry_link}}" style="background-color: #dc3545; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;">Retry Payment</a>
        </div>
        
        <p style="margin-top: 30px; color: #666; font-size: 12px;">
            Best regards,<br>
            {{app_name}} Team
        </p>
    </div>
</body>
</html>
HTML;
    }

    private function getPaymentRefundedEmailTemplate(): string
    {
        return <<<'HTML'
<html>
<body style="font-family: Arial, sans-serif; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h2 style="color: #28a745;">Refund Processed</h2>
        <p>Dear {{customer_name}},</p>
        <p>Your refund has been successfully processed and will appear in your account within 3-5 business days.</p>
        
        <h3 style="color: #007bff; border-bottom: 2px solid #007bff; padding-bottom: 10px;">Refund Details</h3>
        <table style="width: 100%; margin: 20px 0; border-collapse: collapse;">
            <tr style="background-color: #f9f9f9;">
                <td style="padding: 12px; border-bottom: 1px solid #ddd;"><strong>Order Number:</strong></td>
                <td style="padding: 12px; border-bottom: 1px solid #ddd;">{{order_number}}</td>
            </tr>
            <tr>
                <td style="padding: 12px; border-bottom: 1px solid #ddd;"><strong>Refund Amount:</strong></td>
                <td style="padding: 12px; border-bottom: 1px solid #ddd; color: #28a745; font-weight: bold;">{{refund_amount}}</td>
            </tr>
            <tr style="background-color: #f9f9f9;">
                <td style="padding: 12px; border-bottom: 1px solid #ddd;"><strong>Refund Reason:</strong></td>
                <td style="padding: 12px; border-bottom: 1px solid #ddd;">{{refund_reason}}</td>
            </tr>
            <tr>
                <td style="padding: 12px; border-bottom: 1px solid #ddd;"><strong>Transaction ID:</strong></td>
                <td style="padding: 12px; border-bottom: 1px solid #ddd;">{{transaction_id}}</td>
            </tr>
            <tr style="background-color: #f9f9f9;">
                <td style="padding: 12px;"><strong>Status:</strong></td>
                <td style="padding: 12px; color: #28a745; font-weight: bold;">Processed</td>
            </tr>
        </table>

        <p style="margin-top: 20px; color: #666;">
            If you have any questions about this refund, please contact our support team.
        </p>

        <div style="margin-top: 30px; text-align: center;">
            <a href="{{support_link}}" style="background-color: #007bff; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;">Contact Support</a>
        </div>
        
        <p style="margin-top: 30px; color: #666; font-size: 12px;">
            Best regards,<br>
            {{app_name}} Team
        </p>
    </div>
</body>
</html>
HTML;
    }
}
