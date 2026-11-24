<?php

namespace Modules\Orders\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Notifications\Models\NotificationEvent;
use Modules\Notifications\Models\NotificationChannel;
use Modules\Notifications\Models\NotificationMapping;
use Modules\EmailNotification\Models\NotificationTemplate as EmailTemplate;
use Modules\EmailNotification\Support\DawatEmail;
use Modules\WebPushNotification\Models\NotificationTemplate as WebPushTemplate;

class OrderNotificationSeeder extends Seeder
{
    public function run(): void
    {
        \Illuminate\Support\Facades\DB::transaction(function () {
            // Create missing channels without changing existing credentials or enabled flags.
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

            foreach ($this->definitions() as $eventCode => $definition) {
                $eventData = [
                    'event_name' => $definition['name'],
                    'event_description' => $definition['description'],
                ];
                if (isset($definition['notification_class'])) {
                    $eventData['notification_class'] = $definition['notification_class'];
                }
                $event = NotificationEvent::updateOrCreate(['event_code' => $eventCode], $eventData);
                $email = EmailTemplate::updateOrCreate([
                    'name' => $definition['email_name'], 'locale' => 'en',
                ], [
                    'subject' => $definition['email_subject'],
                    'content' => $definition['email_content'], 'status' => true,
                ]);
                $push = WebPushTemplate::updateOrCreate(['name' => $definition['push_name']], [
                    'subject' => $definition['push_subject'], 'body' => $definition['push_body'],
                    // This column already has an array cast; do not JSON-encode it again.
                    'variables' => $definition['variables'], 'is_active' => true,
                ]);

                foreach (['email' => $email, 'webpush' => $push] as $channel => $template) {
                    NotificationMapping::updateOrCreate([
                        'event_id' => $event->id, 'channel_id' => $channels[$channel]->id,
                    ], [
                        'template_id' => (string) $template->id,
                        // Both handlers read recipients; custom_config is not a recipient field.
                        'recipients' => [
                            'notify_customer' => true, 'notify_admin' => false, 'notify_vendor' => false,
                        ],
                    ]);
                }
            }
        });
        $this->command?->info('Order email and web-push templates mapped to customer recipients.');
    }

    /** Stable names preserve existing mappings and support repeatable standalone seeding. */
    protected function definitions(): array
    {
        return [
            'order_created' => [
                'name' => 'Order Created',
                'description' => 'Triggered when a new order is created',
                'notification_class' => \Modules\Orders\Notifications\OrderCreatedNotification::class,
                'email_name' => 'order_created_customer',
                'email_subject' => 'Order Confirmation - {{order_number}}',
                'email_content' => $this->getOrderCreatedEmailTemplate(),
                'push_name' => 'order_created',
                'push_subject' => 'Order Received',
                'push_body' => 'Your order {{order_number}} has been received. Total: {{order_total}}',
                'variables' => ['order_number' => 'Order number', 'order_total' => 'Order total amount'],
            ],
            'order_status_updated' => [
                'name' => 'Order Status Updated',
                'description' => 'Triggered when order status changes',
                'notification_class' => \Modules\Orders\Notifications\OrderStatusUpdateNotification::class,
                'email_name' => 'order_status_updated',
                'email_subject' => 'Order {{order_number}} Status Updated',
                'email_content' => $this->getOrderStatusEmailTemplate(),
                'push_name' => 'order_status_update',
                'push_subject' => 'Order Update',
                'push_body' => 'Your order {{order_number}} status is now {{new_status}}.',
                'variables' => ['order_number' => 'Order number', 'new_status' => 'New order status'],
            ],
            'order_confirmed' => [
                'notification_class' => \Modules\Orders\Notifications\OrderConfirmedNotification::class,
                'name' => 'Order Confirmed',
                'description' => 'Triggered when restaurant confirms the order',
                'email_name' => 'order_confirmed',
                'email_subject' => 'Order Confirmed - {{order_number}}',
                'email_content' => $this->getOrderConfirmedEmailTemplate(),
                'push_name' => 'order_confirmed',
                'push_subject' => 'Order Confirmed',
                'push_body' => 'The restaurant has accepted your order {{order_number}}.',
                'variables' => ['order_number' => 'Order number'],
            ],
            'order_shipped' => [
                'notification_class' => \Modules\Orders\Notifications\OrderShippedNotification::class,
                'name' => 'Order Shipped',
                'description' => 'Triggered when order is out for delivery',
                'email_name' => 'order_shipped',
                'email_subject' => 'Order Shipped - {{order_number}}',
                'email_content' => $this->getOrderShippedEmailTemplate(),
                'push_name' => 'order_shipped',
                'push_subject' => 'Out for Delivery',
                'push_body' => 'Your order {{order_number}} is on the way. Expected delivery: {{estimated_delivery}}',
                'variables' => ['order_number' => 'Order number', 'estimated_delivery' => 'Estimated delivery time'],
            ],
            'order_delivered' => [
                'notification_class' => \Modules\Orders\Notifications\OrderDeliveredNotification::class,
                'name' => 'Order Delivered',
                'description' => 'Triggered when order is successfully delivered',
                'email_name' => 'order_delivered',
                'email_subject' => 'Order Delivered - {{order_number}}',
                'email_content' => $this->getOrderDeliveredEmailTemplate(),
                'push_name' => 'order_delivered',
                'push_subject' => 'Order Delivered',
                'push_body' => 'Your order {{order_number}} has been delivered. Enjoy your meal!',
                'variables' => ['order_number' => 'Order number'],
            ],
            'order_cancelled' => [
                'notification_class' => \Modules\Orders\Notifications\OrderCancelledNotification::class,
                'name' => 'Order Cancelled',
                'description' => 'Triggered when an order is cancelled',
                'email_name' => 'order_cancelled',
                'email_subject' => 'Order Cancelled - {{order_number}}',
                'email_content' => $this->getOrderCancelledEmailTemplate(),
                'push_name' => 'order_cancelled',
                'push_subject' => 'Order Cancelled',
                'push_body' => 'Your order {{order_number}} has been cancelled. Reason: {{cancellation_reason}}',
                'variables' => ['order_number' => 'Order number', 'cancellation_reason' => 'Reason for cancellation'],
            ],
        ];
    }

    private function links(): string
    {
        return 'Questions about your order? <a href="{{support_url}}" style="color:#ff9a66;text-decoration:underline;">Contact support</a> &middot; <a href="{{privacy_url}}" style="color:#ff9a66;text-decoration:underline;">Privacy policy</a>';
    }

    protected function getOrderCreatedEmailTemplate(): string
    {
        return DawatEmail::render([
            'title' => 'Order confirmation — Dawat',
            'preheader' => 'Thank you for your order {{order_number}}. We have received it and will start preparing it shortly.',
            'badge' => 'ORDER RECEIVED',
            'headline' => 'Thank you<br>for your order',
            'greeting' => 'Hello {{user_name}},',
            'intro' => "We've received your order and will start preparing it shortly. Here is a summary for your records.",
            'blocks' => [
                DawatEmail::code('Order number', '{{order_number}}', 'Placed on <strong style="color:#ff9a66;">{{order_date}}</strong>', '2px'),
                DawatEmail::details('Order summary', [
                    'Items' => '<span style="white-space:pre-line;">{{order_items}}</span>',
                    'Subtotal' => '{{order_subtotal}}',
                    'Discount' => '- {{order_discount}}',
                    'Tax' => '{{order_tax}}',
                    'Delivery fee' => '{{delivery_fee}}',
                    'Service charge' => '{{service_charge}}',
                    'Event guests' => '{{event_guest_count}}',
                    'Delivery' => '{{delivery_label}}',
                    'Distance' => '{{delivery_distance_miles}}',
                    'Estimated duration' => '{{estimated_delivery_minutes}}',
                ], ['Total' => '{{order_total}}']),
                DawatEmail::details('Delivery information', [
                    'Billing address' => '{{billing_name}}<br>{{billing_street}}<br>{{billing_city}}, {{billing_state}} {{billing_zip}}<br>{{billing_country}}',
                    'Shipping address' => '{{shipping_name}}<br>{{shipping_street}}<br>{{shipping_city}}, {{shipping_state}} {{shipping_zip}}<br>{{shipping_country}}',
                ]),
                DawatEmail::callout('Stay updated', "You'll receive notifications about your order status by email and push notification."),
            ],
            'buttons' => [DawatEmail::button('Track your order', '{{order_link}}')],
            'notes' => $this->links(),
            'footer' => 'You received this email because you placed an order on Dawat.',
        ]);
    }

    protected function getOrderStatusEmailTemplate(): string
    {
        return DawatEmail::render([
            'title' => 'Order status update — Dawat',
            'preheader' => 'Your order {{order_number}} is now {{new_status}}.',
            'badge' => 'ORDER UPDATE',
            'headline' => 'Your order<br>has an update',
            'greeting' => 'Hello {{user_name}},',
            'intro' => 'We have an update on the status of your order.',
            'blocks' => [
                DawatEmail::details('Order information', [
                    'Order number' => '{{order_number}}',
                    'Previous status' => '{{old_status}}',
                    'Current status' => '<strong style="color:#ff9a66;">{{new_status}}</strong>',
                    'Items' => '<span style="white-space:pre-line;">{{order_items}}</span>',
                    'Event guests' => '{{event_guest_count}}',
                    'Delivery' => '{{delivery_label}}',
                    'Distance' => '{{delivery_distance_miles}}',
                    'Estimated duration' => '{{estimated_delivery_minutes}}',
                    'Order total' => '{{order_total}}',
                ]),
            ],
            'buttons' => [DawatEmail::button('View order details', '{{order_link}}')],
            'notes' => $this->links(),
            'footer' => 'You received this email because you placed an order on Dawat.',
        ]);
    }

    private function getOrderConfirmedEmailTemplate(): string
    {
        return DawatEmail::render([
            'title' => 'Order confirmed — Dawat',
            'preheader' => 'The restaurant confirmed your order {{order_number}} and is preparing it now.',
            'badge' => 'ORDER CONFIRMED',
            'headline' => 'Your food is<br>being prepared',
            'greeting' => 'Hello {{user_name}},',
            'intro' => 'Great news! The restaurant has confirmed your order and is now preparing your meal.',
            'blocks' => [
                DawatEmail::details('Order details', [
                    'Order number' => '{{order_number}}',
                    'Status' => 'Confirmed',
                    'Estimated time' => '{{estimated_preparation_time}}',
                ]),
                DawatEmail::callout('Prepared with care', 'Your food is being prepared now. We will let you know when it is on the way.'),
            ],
            'buttons' => [DawatEmail::button('Track your order', '{{order_link}}')],
            'notes' => $this->links(),
            'footer' => 'You received this email because you placed an order on Dawat.',
        ]);
    }

    private function getOrderShippedEmailTemplate(): string
    {
        return DawatEmail::render([
            'title' => 'Order on the way — Dawat',
            'preheader' => 'Your order {{order_number}} is out for delivery. Expected: {{estimated_delivery}}.',
            'badge' => 'OUT FOR DELIVERY',
            'headline' => 'Your order<br>is on the way',
            'greeting' => 'Hello {{user_name}},',
            'intro' => 'Your order is now on its way to you. Get ready to enjoy your meal!',
            'blocks' => [
                DawatEmail::details('Delivery information', [
                    'Order number' => '{{order_number}}',
                    'Tracking number' => '{{tracking_number}}',
                    'Delivery partner' => '{{delivery_partner}}',
                    'Estimated delivery' => '{{estimated_delivery}}',
                    'Delivery address' => '{{delivery_address}}',
                ]),
            ],
            'buttons' => [
                DawatEmail::button('Track live', '{{tracking_link}}'),
                DawatEmail::button('View order', '{{order_link}}'),
            ],
            'notes' => $this->links(),
            'footer' => 'You received this email because you placed an order on Dawat.',
        ]);
    }

    private function getOrderDeliveredEmailTemplate(): string
    {
        return DawatEmail::render([
            'title' => 'Order delivered — Dawat',
            'preheader' => 'Your order {{order_number}} has been delivered. Enjoy your meal!',
            'badge' => 'ORDER DELIVERED',
            'headline' => 'Delivered.<br>Enjoy your meal',
            'greeting' => 'Hello {{user_name}},',
            'intro' => 'Your order has been delivered. We hope you enjoy every bite.',
            'blocks' => [
                DawatEmail::details('Delivery details', [
                    'Order number' => '{{order_number}}',
                    'Delivered on' => '{{delivery_date}}',
                    'Status' => 'Delivered successfully',
                ]),
                DawatEmail::callout('Rate your experience', 'How was your meal? Your feedback helps us improve and helps other customers choose. <a href="{{review_link}}" style="color:#ff9a66;text-decoration:underline;">Write a review</a>.'),
            ],
            'buttons' => [DawatEmail::button('View order details', '{{order_link}}')],
            'notes' => ['Thank you for choosing Dawat. We look forward to serving you again soon.', $this->links()],
            'footer' => 'You received this email because you placed an order on Dawat.',
        ]);
    }

    private function getOrderCancelledEmailTemplate(): string
    {
        return DawatEmail::render([
            'title' => 'Order cancelled — Dawat',
            'preheader' => 'Your order {{order_number}} has been cancelled. Reason: {{cancellation_reason}}.',
            'badge' => 'ORDER CANCELLED',
            'headline' => 'Your order<br>was cancelled',
            'greeting' => 'Hello {{user_name}},',
            'intro' => "We're sorry to let you know that your order has been cancelled.",
            'blocks' => [
                DawatEmail::details('Cancellation details', [
                    'Order number' => '{{order_number}}',
                    'Cancelled on' => '{{cancelled_date}}',
                    'Order total' => '{{order_total}}',
                    'Reason' => '{{cancellation_reason}}',
                ]),
                DawatEmail::callout('Refund information', 'If you already paid for this order, your refund will be processed within <strong style="color:#ffffff;">{{refund_processing_days}} business days</strong> and credited back to your original payment method.'),
            ],
            'buttons' => [
                DawatEmail::button('Browse menu', '{{browse_menu_link}}'),
                DawatEmail::button('Contact support', '{{support_url}}'),
            ],
            'notes' => ["We're sorry for the inconvenience and would love to serve you again soon.", $this->links()],
            'footer' => 'You received this email because you placed an order on Dawat.',
        ]);
    }
}
