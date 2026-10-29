<?php

namespace Modules\Orders\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Notifications\Models\NotificationEvent;
use Modules\Notifications\Models\NotificationChannel;
use Modules\Notifications\Models\NotificationMapping;
use Modules\EmailNotification\Models\NotificationTemplate as EmailTemplate;
use Modules\WebPushNotification\Models\NotificationTemplate as WebPushTemplate;

class OrderNotificationSeeder extends Seeder
{
    public function run(): void
    {
        // Create Events with correct column names
        $orderCreatedEvent = NotificationEvent::updateOrCreate(
            ['event_code' => 'order_created'],
            [
                'event_name' => 'Order Created',
                'event_description' => 'Triggered when a new order is created',
            ]
        );

        $orderStatusEvent = NotificationEvent::updateOrCreate(
            ['event_code' => 'order_status_updated'],
            [
                'event_name' => 'Order Status Updated',
                'event_description' => 'Triggered when order status changes',
            ]
        );

        $orderConfirmedEvent = NotificationEvent::updateOrCreate(
            ['event_code' => 'order_confirmed'],
            [
                'event_name' => 'Order Confirmed',
                'event_description' => 'Triggered when restaurant confirms the order',
            ]
        );

        $orderShippedEvent = NotificationEvent::updateOrCreate(
            ['event_code' => 'order_shipped'],
            [
                'event_name' => 'Order Shipped',
                'event_description' => 'Triggered when order is shipped/out for delivery',
            ]
        );

        $orderDeliveredEvent = NotificationEvent::updateOrCreate(
            ['event_code' => 'order_delivered'],
            [
                'event_name' => 'Order Delivered',
                'event_description' => 'Triggered when order is successfully delivered',
            ]
        );

        $orderCancelledEvent = NotificationEvent::updateOrCreate(
            ['event_code' => 'order_cancelled'],
            [
                'event_name' => 'Order Cancelled',
                'event_description' => 'Triggered when an order is cancelled',
            ]
        );

        // Create Email Templates
        $orderCreatedEmailTemplate = EmailTemplate::updateOrCreate(
            ['name' => 'order_created_customer'],
            [
                'subject' => 'Order Confirmation - {{order_number}}',
                'content' => $this->getOrderCreatedEmailTemplate(),
                'status' => true,
                'locale' => 'en',
            ]
        );

        $orderStatusEmailTemplate = EmailTemplate::updateOrCreate(
            ['name' => 'order_status_updated'],
            [
                'subject' => 'Order {{order_number}} Status Updated',
                'content' => $this->getOrderStatusEmailTemplate(),
                'status' => true,
                'locale' => 'en',
            ]
        );

        $orderConfirmedEmailTemplate = EmailTemplate::updateOrCreate(
            ['name' => 'order_confirmed'],
            [
                'subject' => 'Order Confirmed - {{order_number}}',
                'content' => $this->getOrderConfirmedEmailTemplate(),
                'status' => true,
                'locale' => 'en',
            ]
        );

        $orderShippedEmailTemplate = EmailTemplate::updateOrCreate(
            ['name' => 'order_shipped'],
            [
                'subject' => 'Order Shipped - {{order_number}}',
                'content' => $this->getOrderShippedEmailTemplate(),
                'status' => true,
                'locale' => 'en',
            ]
        );

        $orderDeliveredEmailTemplate = EmailTemplate::updateOrCreate(
            ['name' => 'order_delivered'],
            [
                'subject' => 'Order Delivered - {{order_number}}',
                'content' => $this->getOrderDeliveredEmailTemplate(),
                'status' => true,
                'locale' => 'en',
            ]
        );

        $orderCancelledEmailTemplate = EmailTemplate::updateOrCreate(
            ['name' => 'order_cancelled'],
            [
                'subject' => 'Order Cancelled - {{order_number}}',
                'content' => $this->getOrderCancelledEmailTemplate(),
                'status' => true,
                'locale' => 'en',
            ]
        );

        // Create WebPush Templates
        $orderCreatedPushTemplate = WebPushTemplate::updateOrCreate(
            ['name' => 'order_created'],
            [
                'subject' => 'Order Confirmed',
                'body' => '🎉 Your order {{order_number}} has been confirmed. Total: {{order_total}}',
                'variables' => json_encode([
                    'order_number' => 'Order number',
                    'order_total' => 'Order total amount',
                ]),
                'is_active' => true,
            ]
        );

        $orderStatusPushTemplate = WebPushTemplate::updateOrCreate(
            ['name' => 'order_status_update'],
            [
                'subject' => 'Order Update',
                'body' => '📦 Your order {{order_number}} status is now {{order_status}}',
                'variables' => json_encode([
                    'order_number' => 'Order number',
                    'order_status' => 'Order status',
                ]),
                'is_active' => true,
            ]
        );

        $orderConfirmedPushTemplate = WebPushTemplate::updateOrCreate(
            ['name' => 'order_confirmed'],
            [
                'subject' => 'Order Confirmed',
                'body' => '✅ Restaurant confirmed your order {{order_number}}. Preparing your food now!',
                'variables' => json_encode([
                    'order_number' => 'Order number',
                ]),
                'is_active' => true,
            ]
        );

        $orderShippedPushTemplate = WebPushTemplate::updateOrCreate(
            ['name' => 'order_shipped'],
            [
                'subject' => 'Order Shipped',
                'body' => '🚚 Your order {{order_number}} is on the way! Expected delivery: {{estimated_delivery}}',
                'variables' => json_encode([
                    'order_number' => 'Order number',
                    'estimated_delivery' => 'Estimated delivery time',
                ]),
                'is_active' => true,
            ]
        );

        $orderDeliveredPushTemplate = WebPushTemplate::updateOrCreate(
            ['name' => 'order_delivered'],
            [
                'subject' => 'Order Delivered',
                'body' => '🎊 Your order {{order_number}} has been delivered. Enjoy your meal!',
                'variables' => json_encode([
                    'order_number' => 'Order number',
                ]),
                'is_active' => true,
            ]
        );

        $orderCancelledPushTemplate = WebPushTemplate::updateOrCreate(
            ['name' => 'order_cancelled'],
            [
                'subject' => 'Order Cancelled',
                'body' => '❌ Your order {{order_number}} has been cancelled. Reason: {{cancellation_reason}}',
                'variables' => json_encode([
                    'order_number' => 'Order number',
                    'cancellation_reason' => 'Reason for cancellation',
                ]),
                'is_active' => true,
            ]
        );

        // Get Channels
        $emailChannel = NotificationChannel::where('name', 'email')->first();
        $webpushChannel = NotificationChannel::where('name', 'webpush')->first();

        // Create Mappings for Order Created
        if ($emailChannel) {
            NotificationMapping::updateOrCreate(
                [
                    'event_id' => $orderCreatedEvent->id,
                    'channel_id' => $emailChannel->id,
                ],
                [
                    'template_id' => (string)$orderCreatedEmailTemplate->id,
                    'priority' => 1,
                    'custom_config' => json_encode([
                        'notify_admin' => true,
                        'notify_customer' => true,
                        'notify_warehouse' => true,
                    ]),
                ]
            );
        }

        if ($webpushChannel) {
            NotificationMapping::updateOrCreate(
                [
                    'event_id' => $orderCreatedEvent->id,
                    'channel_id' => $webpushChannel->id,
                ],
                [
                    'template_id' => (string)$orderCreatedPushTemplate->id,
                    'priority' => 2,
                    'custom_config' => json_encode([
                        'notify_admin' => true,
                        'notify_customer' => true,
                    ]),
                ]
            );
        }

        // Create Mappings for Order Status Updated
        if ($emailChannel) {
            NotificationMapping::updateOrCreate(
                [
                    'event_id' => $orderStatusEvent->id,
                    'channel_id' => $emailChannel->id,
                ],
                [
                    'template_id' => (string)$orderStatusEmailTemplate->id,
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
                    'event_id' => $orderStatusEvent->id,
                    'channel_id' => $webpushChannel->id,
                ],
                [
                    'template_id' => (string)$orderStatusPushTemplate->id,
                    'priority' => 2,
                    'custom_config' => json_encode([
                        'notify_customer' => true,
                    ]),
                ]
            );
        }

        // Create Mappings for Order Confirmed
        if ($emailChannel) {
            NotificationMapping::updateOrCreate(
                [
                    'event_id' => $orderConfirmedEvent->id,
                    'channel_id' => $emailChannel->id,
                ],
                [
                    'template_id' => (string)$orderConfirmedEmailTemplate->id,
                    'priority' => 1,
                    'custom_config' => json_encode([
                        'notify_customer' => true,
                    ]),
                ]
            );
        }

        if ($webpushChannel) {
            NotificationMapping::updateOrCreate(
                [
                    'event_id' => $orderConfirmedEvent->id,
                    'channel_id' => $webpushChannel->id,
                ],
                [
                    'template_id' => (string)$orderConfirmedPushTemplate->id,
                    'priority' => 2,
                    'custom_config' => json_encode([
                        'notify_customer' => true,
                    ]),
                ]
            );
        }

        // Create Mappings for Order Shipped
        if ($emailChannel) {
            NotificationMapping::updateOrCreate(
                [
                    'event_id' => $orderShippedEvent->id,
                    'channel_id' => $emailChannel->id,
                ],
                [
                    'template_id' => (string)$orderShippedEmailTemplate->id,
                    'priority' => 1,
                    'custom_config' => json_encode([
                        'notify_customer' => true,
                    ]),
                ]
            );
        }

        if ($webpushChannel) {
            NotificationMapping::updateOrCreate(
                [
                    'event_id' => $orderShippedEvent->id,
                    'channel_id' => $webpushChannel->id,
                ],
                [
                    'template_id' => (string)$orderShippedPushTemplate->id,
                    'priority' => 2,
                    'custom_config' => json_encode([
                        'notify_customer' => true,
                    ]),
                ]
            );
        }

        // Create Mappings for Order Delivered
        if ($emailChannel) {
            NotificationMapping::updateOrCreate(
                [
                    'event_id' => $orderDeliveredEvent->id,
                    'channel_id' => $emailChannel->id,
                ],
                [
                    'template_id' => (string)$orderDeliveredEmailTemplate->id,
                    'priority' => 1,
                    'custom_config' => json_encode([
                        'notify_customer' => true,
                    ]),
                ]
            );
        }

        if ($webpushChannel) {
            NotificationMapping::updateOrCreate(
                [
                    'event_id' => $orderDeliveredEvent->id,
                    'channel_id' => $webpushChannel->id,
                ],
                [
                    'template_id' => (string)$orderDeliveredPushTemplate->id,
                    'priority' => 2,
                    'custom_config' => json_encode([
                        'notify_customer' => true,
                    ]),
                ]
            );
        }

        // Create Mappings for Order Cancelled
        if ($emailChannel) {
            NotificationMapping::updateOrCreate(
                [
                    'event_id' => $orderCancelledEvent->id,
                    'channel_id' => $emailChannel->id,
                ],
                [
                    'template_id' => (string)$orderCancelledEmailTemplate->id,
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
                    'event_id' => $orderCancelledEvent->id,
                    'channel_id' => $webpushChannel->id,
                ],
                [
                    'template_id' => (string)$orderCancelledPushTemplate->id,
                    'priority' => 2,
                    'custom_config' => json_encode([
                        'notify_customer' => true,
                    ]),
                ]
            );
        }
    }

    private function getOrderCreatedEmailTemplate(): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f4f7fa; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">
    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="background-color: #f4f7fa;">
        <tr>
            <td style="padding: 40px 20px;">
                <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                    <!-- Header with Logo -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 40px 30px; text-align: center; border-radius: 12px 12px 0 0;">
                            <img src="{{company_logo}}" alt="{{app_name}}" style="max-width: 180px; height: auto; margin-bottom: 20px;" />
                            <h1 style="margin: 0; color: #ffffff; font-size: 28px; font-weight: 600; text-shadow: 0 2px 4px rgba(0,0,0,0.1);">🎉 Order Confirmed!</h1>
                            <p style="margin: 10px 0 0; color: #ffffff; font-size: 16px; opacity: 0.95;">Thank you for your order</p>
                        </td>
                    </tr>
                    
                    <!-- Content -->
                    <tr>
                        <td style="padding: 40px 30px;">
                            <p style="margin: 0 0 10px; color: #333333; font-size: 16px; line-height: 1.6;">Dear {{user_name}},</p>
                            <p style="margin: 0 0 30px; color: #555555; font-size: 15px; line-height: 1.6;">Thank you for your order! We've received your order and will start preparing it shortly.</p>
                            
                            <!-- Order Summary Box -->
                            <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 25px; margin-bottom: 30px; border-radius: 8px; text-align: center;">
                                <p style="margin: 0 0 5px; color: #ffffff; font-size: 14px; opacity: 0.9;">Order Number</p>
                                <p style="margin: 0 0 20px; color: #ffffff; font-size: 28px; font-weight: 700; letter-spacing: 1px;">{{order_number}}</p>
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="border-top: 1px solid rgba(255,255,255,0.3); padding-top: 15px;">
                                    <tr>
                                        <td style="text-align: center; padding: 10px;">
                                            <p style="margin: 0; color: #ffffff; font-size: 13px; opacity: 0.85;">Order Date</p>
                                            <p style="margin: 5px 0 0; color: #ffffff; font-size: 16px; font-weight: 600;">{{order_date}}</p>
                                        </td>
                                        <td style="text-align: center; padding: 10px; border-left: 1px solid rgba(255,255,255,0.3);">
                                            <p style="margin: 0; color: #ffffff; font-size: 13px; opacity: 0.85;">Total Amount</p>
                                            <p style="margin: 5px 0 0; color: #ffffff; font-size: 20px; font-weight: 700;">{{order_total}}</p>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            
                            <!-- Order Items -->
                            <div style="background-color: #f8f9fa; border-left: 4px solid #667eea; padding: 20px; margin-bottom: 30px; border-radius: 4px;">
                                <h2 style="margin: 0 0 20px; color: #667eea; font-size: 20px; font-weight: 600;">📦 Order Items</h2>
                                
                                <div style="padding: 15px; background-color: #fff; border-radius: 4px; margin-bottom: 15px;">
                                    <p style="margin: 0; color: #333333; font-size: 14px; line-height: 1.8; white-space: pre-line;">{{order_items}}</p>
                                </div>
                                
                                <!-- Order Summary -->
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="border-collapse: collapse; margin-top: 20px; border-top: 2px solid #e9ecef; padding-top: 15px;">
                                    <tr>
                                        <td style="padding: 8px 0; color: #666666; font-size: 14px; text-align: right;">Subtotal:</td>
                                        <td style="padding: 8px 0; color: #333333; font-size: 14px; text-align: right; width: 120px;">{{order_subtotal}}</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 8px 0; color: #666666; font-size: 14px; text-align: right;">Discount:</td>
                                        <td style="padding: 8px 0; color: #4caf50; font-size: 14px; text-align: right; width: 120px;">- {{order_discount}}</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 8px 0; color: #666666; font-size: 14px; text-align: right;">Tax:</td>
                                        <td style="padding: 8px 0; color: #333333; font-size: 14px; text-align: right; width: 120px;">{{order_tax}}</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 8px 0; color: #666666; font-size: 14px; text-align: right;">Delivery Fee:</td>
                                        <td style="padding: 8px 0; color: #333333; font-size: 14px; text-align: right; width: 120px;">{{delivery_fee}}</td>
                                    </tr>
                                    <tr style="border-top: 2px solid #667eea;">
                                        <td style="padding: 12px 0; color: #667eea; font-size: 16px; font-weight: 700; text-align: right;">Total:</td>
                                        <td style="padding: 12px 0; color: #667eea; font-size: 18px; font-weight: 700; text-align: right; width: 120px;">{{order_total}}</td>
                                    </tr>
                                </table>
                            </div>

                            <!-- Delivery Information -->
                            <div style="background-color: #fff; border: 2px solid #e9ecef; padding: 20px; margin-bottom: 20px; border-radius: 8px;">
                                <h3 style="margin: 0 0 15px; color: #333333; font-size: 18px; font-weight: 600;">📍 Delivery Information</h3>
                                
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                                    <tr>
                                        <td style="width: 50%; padding-right: 10px; vertical-align: top;">
                                            <h4 style="margin: 0 0 10px; color: #667eea; font-size: 14px; font-weight: 600;">Billing Address</h4>
                                            <p style="margin: 0; color: #555555; font-size: 13px; line-height: 1.6;">
                                                {{billing_name}}<br>
                                                {{billing_street}}<br>
                                                {{billing_city}}, {{billing_state}} {{billing_zip}}<br>
                                                {{billing_country}}
                                            </p>
                                        </td>
                                        <td style="width: 50%; padding-left: 10px; vertical-align: top;">
                                            <h4 style="margin: 0 0 10px; color: #667eea; font-size: 14px; font-weight: 600;">Shipping Address</h4>
                                            <p style="margin: 0; color: #555555; font-size: 13px; line-height: 1.6;">
                                                {{shipping_name}}<br>
                                                {{shipping_street}}<br>
                                                {{shipping_city}}, {{shipping_state}} {{shipping_zip}}<br>
                                                {{shipping_country}}
                                            </p>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            
                            <!-- CTA Button -->
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                                <tr>
                                    <td style="text-align: center; padding: 20px 0;">
                                        <a href="{{order_link}}" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #ffffff; padding: 16px 40px; text-decoration: none; border-radius: 50px; display: inline-block; font-weight: 600; font-size: 16px; box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);">Track Your Order</a>
                                    </td>
                                </tr>
                            </table>
                            
                            <div style="background-color: #e8f5e9; border-left: 4px solid #4caf50; padding: 15px; margin-top: 20px; border-radius: 4px;">
                                <p style="margin: 0; color: #2e7d32; font-size: 13px; line-height: 1.6;">📱 <strong>Stay Updated:</strong> You'll receive notifications about your order status via email and push notifications.</p>
                            </div>
                        </td>
                    </tr>
                    
                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f8f9fa; padding: 30px; text-align: center; border-radius: 0 0 12px 12px; border-top: 1px solid #e9ecef;">
                            <p style="margin: 0 0 10px; color: #666666; font-size: 14px;">Best regards,<br><strong>{{app_name}} Team</strong></p>
                            <p style="margin: 0; color: #999999; font-size: 12px;">&copy; {{current_year}} {{app_name}}. All rights reserved.</p>
                            <p style="margin: 10px 0 0; color: #999999; font-size: 12px;">
                                <a href="{{support_url}}" style="color: #667eea; text-decoration: none;">Support</a> | 
                                <a href="{{privacy_url}}" style="color: #667eea; text-decoration: none;">Privacy Policy</a>
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;
    }

    private function getOrderStatusEmailTemplate(): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Status Update</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f4f7fa; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">
    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="background-color: #f4f7fa;">
        <tr>
            <td style="padding: 40px 20px;">
                <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                    <!-- Header with Logo -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 40px 30px; text-align: center; border-radius: 12px 12px 0 0;">
                            <img src="{{company_logo}}" alt="{{app_name}}" style="max-width: 180px; height: auto; margin-bottom: 20px;" />
                            <h1 style="margin: 0; color: #ffffff; font-size: 28px; font-weight: 600; text-shadow: 0 2px 4px rgba(0,0,0,0.1);">📦 Order Status Update</h1>
                        </td>
                    </tr>
                    
                    <!-- Content -->
                    <tr>
                        <td style="padding: 40px 30px;">
                            <p style="margin: 0 0 10px; color: #333333; font-size: 16px; line-height: 1.6;">Dear {{user_name}},</p>
                            <p style="margin: 0 0 30px; color: #555555; font-size: 15px; line-height: 1.6;">We have an update on your order status.</p>
                            
                            <div style="background-color: #f8f9fa; border-left: 4px solid #667eea; padding: 20px; margin-bottom: 30px; border-radius: 4px;">
                                <h2 style="margin: 0 0 20px; color: #667eea; font-size: 20px; font-weight: 600;">Order Information</h2>
                                
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="border-collapse: collapse;">
                                    <tr>
                                        <td style="padding: 12px 0; border-bottom: 1px solid #e9ecef; color: #666666; font-size: 14px; width: 40%;"><strong>Order Number:</strong></td>
                                        <td style="padding: 12px 0; border-bottom: 1px solid #e9ecef; color: #333333; font-size: 14px;">{{order_number}}</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 12px 0; border-bottom: 1px solid #e9ecef; color: #666666; font-size: 14px;"><strong>Previous Status:</strong></td>
                                        <td style="padding: 12px 0; border-bottom: 1px solid #e9ecef; color: #999999; font-size: 14px;">{{old_status}}</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 12px 0; border-bottom: 1px solid #e9ecef; color: #666666; font-size: 14px;"><strong>Current Status:</strong></td>
                                        <td style="padding: 12px 0; border-bottom: 1px solid #e9ecef; font-size: 14px;"><strong style="color: #4caf50;">{{new_status}}</strong></td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 12px 0; color: #666666; font-size: 14px;"><strong>Order Total:</strong></td>
                                        <td style="padding: 12px 0; color: #333333; font-size: 14px; font-weight: 600;">{{order_total}}</td>
                                    </tr>
                                </table>
                            </div>
                            
                          
                            
                            <!-- CTA Button -->
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                                <tr>
                                    <td style="text-align: center; padding: 20px 0;">
                                        <a href="{{order_link}}" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #ffffff; padding: 16px 40px; text-decoration: none; border-radius: 50px; display: inline-block; font-weight: 600; font-size: 16px; box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);">View Order Details</a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    
                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f8f9fa; padding: 30px; text-align: center; border-radius: 0 0 12px 12px; border-top: 1px solid #e9ecef;">
                            <p style="margin: 0 0 10px; color: #666666; font-size: 14px;">Best regards,<br><strong>{{app_name}} Team</strong></p>
                            <p style="margin: 0; color: #999999; font-size: 12px;">&copy; {{current_year}} {{app_name}}. All rights reserved.</p>
                            <p style="margin: 10px 0 0; color: #999999; font-size: 12px;">
                                <a href="{{support_url}}" style="color: #667eea; text-decoration: none;">Support</a> | 
                                <a href="{{privacy_url}}" style="color: #667eea; text-decoration: none;">Privacy Policy</a>
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;
    }

    private function getOrderConfirmedEmailTemplate(): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmed</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f4f7fa; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">
    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="background-color: #f4f7fa;">
        <tr>
            <td style="padding: 40px 20px;">
                <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                    <!-- Header with Logo -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); padding: 40px 30px; text-align: center; border-radius: 12px 12px 0 0;">
                            <img src="{{company_logo}}" alt="{{app_name}}" style="max-width: 180px; height: auto; margin-bottom: 20px;" />
                            <h1 style="margin: 0; color: #ffffff; font-size: 28px; font-weight: 600; text-shadow: 0 2px 4px rgba(0,0,0,0.1);">✅ Order Confirmed!</h1>
                            <p style="margin: 10px 0 0; color: #ffffff; font-size: 16px; opacity: 0.95;">Restaurant is preparing your food</p>
                        </td>
                    </tr>
                    
                    <!-- Content -->
                    <tr>
                        <td style="padding: 40px 30px;">
                            <p style="margin: 0 0 10px; color: #333333; font-size: 16px; line-height: 1.6;">Dear {{user_name}},</p>
                            <p style="margin: 0 0 30px; color: #555555; font-size: 15px; line-height: 1.6;">Great news! The restaurant has confirmed your order and is now preparing your delicious meal.</p>
                            
                            <div style="background-color: #e8f5e9; border-left: 4px solid #4caf50; padding: 20px; margin-bottom: 30px; border-radius: 4px;">
                                <h2 style="margin: 0 0 20px; color: #2e7d32; font-size: 20px; font-weight: 600;">Order Details</h2>
                                
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="border-collapse: collapse;">
                                    <tr>
                                        <td style="padding: 12px 0; border-bottom: 1px solid #c8e6c9; color: #2e7d32; font-size: 14px; width: 40%;"><strong>Order Number:</strong></td>
                                        <td style="padding: 12px 0; border-bottom: 1px solid #c8e6c9; color: #333333; font-size: 14px;">{{order_number}}</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 12px 0; border-bottom: 1px solid #c8e6c9; color: #2e7d32; font-size: 14px;"><strong>Status:</strong></td>
                                        <td style="padding: 12px 0; border-bottom: 1px solid #c8e6c9; color: #4caf50; font-size: 14px;"><strong>✓ Confirmed</strong></td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 12px 0; color: #2e7d32; font-size: 14px;"><strong>Estimated Time:</strong></td>
                                        <td style="padding: 12px 0; color: #333333; font-size: 14px;">{{estimated_preparation_time}}</td>
                                    </tr>
                                </table>
                            </div>
                            
                            <div style="background-color: #fff3e0; padding: 15px; border-radius: 8px; margin-bottom: 30px; text-align: center;">
                                <p style="margin: 0; color: #e65100; font-size: 14px;">🍳 <strong>Your food is being prepared with care!</strong></p>
                            </div>
                            
                            <!-- CTA Button -->
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                                <tr>
                                    <td style="text-align: center; padding: 20px 0;">
                                        <a href="{{order_link}}" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); color: #ffffff; padding: 16px 40px; text-decoration: none; border-radius: 50px; display: inline-block; font-weight: 600; font-size: 16px; box-shadow: 0 4px 15px rgba(17, 153, 142, 0.4);">Track Your Order</a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    
                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f8f9fa; padding: 30px; text-align: center; border-radius: 0 0 12px 12px; border-top: 1px solid #e9ecef;">
                            <p style="margin: 0 0 10px; color: #666666; font-size: 14px;">Best regards,<br><strong>{{app_name}} Team</strong></p>
                            <p style="margin: 0; color: #999999; font-size: 12px;">&copy; {{current_year}} {{app_name}}. All rights reserved.</p>
                            <p style="margin: 10px 0 0; color: #999999; font-size: 12px;">
                                <a href="{{support_url}}" style="color: #667eea; text-decoration: none;">Support</a> | 
                                <a href="{{privacy_url}}" style="color: #667eea; text-decoration: none;">Privacy Policy</a>
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;
    }

    private function getOrderShippedEmailTemplate(): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Shipped</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f4f7fa; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">
    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="background-color: #f4f7fa;">
        <tr>
            <td style="padding: 40px 20px;">
                <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                    <!-- Header with Logo -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); padding: 40px 30px; text-align: center; border-radius: 12px 12px 0 0;">
                            <img src="{{company_logo}}" alt="{{app_name}}" style="max-width: 180px; height: auto; margin-bottom: 20px;" />
                            <h1 style="margin: 0; color: #ffffff; font-size: 28px; font-weight: 600; text-shadow: 0 2px 4px rgba(0,0,0,0.1);">🚚 Order On The Way!</h1>
                            <p style="margin: 10px 0 0; color: #ffffff; font-size: 16px; opacity: 0.95;">Your order is out for delivery</p>
                        </td>
                    </tr>
                    
                    <!-- Content -->
                    <tr>
                        <td style="padding: 40px 30px;">
                            <p style="margin: 0 0 10px; color: #333333; font-size: 16px; line-height: 1.6;">Dear {{user_name}},</p>
                            <p style="margin: 0 0 30px; color: #555555; font-size: 15px; line-height: 1.6;">Exciting news! Your order is now on its way to you. Get ready to enjoy your meal!</p>
                            
                            <div style="background-color: #f8f9fa; border-left: 4px solid #f5576c; padding: 20px; margin-bottom: 30px; border-radius: 4px;">
                                <h2 style="margin: 0 0 20px; color: #f5576c; font-size: 20px; font-weight: 600;">Delivery Information</h2>
                                
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="border-collapse: collapse;">
                                    <tr>
                                        <td style="padding: 12px 0; border-bottom: 1px solid #e9ecef; color: #666666; font-size: 14px; width: 40%;"><strong>Order Number:</strong></td>
                                        <td style="padding: 12px 0; border-bottom: 1px solid #e9ecef; color: #333333; font-size: 14px;">{{order_number}}</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 12px 0; border-bottom: 1px solid #e9ecef; color: #666666; font-size: 14px;"><strong>Tracking Number:</strong></td>
                                        <td style="padding: 12px 0; border-bottom: 1px solid #e9ecef; color: #333333; font-size: 14px;">{{tracking_number}}</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 12px 0; border-bottom: 1px solid #e9ecef; color: #666666; font-size: 14px;"><strong>Delivery Partner:</strong></td>
                                        <td style="padding: 12px 0; border-bottom: 1px solid #e9ecef; color: #333333; font-size: 14px;">{{delivery_partner}}</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 12px 0; color: #666666; font-size: 14px;"><strong>Estimated Delivery:</strong></td>
                                        <td style="padding: 12px 0; color: #f5576c; font-size: 14px; font-weight: 600;">{{estimated_delivery}}</td>
                                    </tr>
                                </table>
                            </div>
                            
                            <div style="background-color: #e7f3ff; border-left: 4px solid #2196F3; padding: 15px; margin-bottom: 30px; border-radius: 4px;">
                                <p style="margin: 0; color: #1565c0; font-size: 13px; line-height: 1.6;">📍 <strong>Delivery Address:</strong> {{delivery_address}}</p>
                            </div>
                            
                            <!-- CTA Buttons -->
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                                <tr>
                                    <td style="text-align: center; padding: 20px 0;">
                                        <a href="{{tracking_link}}" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: #ffffff; padding: 16px 40px; text-decoration: none; border-radius: 50px; display: inline-block; font-weight: 600; font-size: 16px; box-shadow: 0 4px 15px rgba(240, 147, 251, 0.4); margin: 0 10px;">Track Live</a>
                                        <a href="{{order_link}}" style="background-color: #ffffff; color: #f5576c; padding: 16px 40px; text-decoration: none; border-radius: 50px; display: inline-block; font-weight: 600; font-size: 16px; border: 2px solid #f5576c; margin: 0 10px;">View Order</a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    
                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f8f9fa; padding: 30px; text-align: center; border-radius: 0 0 12px 12px; border-top: 1px solid #e9ecef;">
                            <p style="margin: 0 0 10px; color: #666666; font-size: 14px;">Best regards,<br><strong>{{app_name}} Team</strong></p>
                            <p style="margin: 0; color: #999999; font-size: 12px;">&copy; {{current_year}} {{app_name}}. All rights reserved.</p>
                            <p style="margin: 10px 0 0; color: #999999; font-size: 12px;">
                                <a href="{{support_url}}" style="color: #667eea; text-decoration: none;">Support</a> | 
                                <a href="{{privacy_url}}" style="color: #667eea; text-decoration: none;">Privacy Policy</a>
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;
    }

    private function getOrderDeliveredEmailTemplate(): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Delivered</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f4f7fa; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">
    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="background-color: #f4f7fa;">
        <tr>
            <td style="padding: 40px 20px;">
                <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                    <!-- Header with Logo -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); padding: 40px 30px; text-align: center; border-radius: 12px 12px 0 0;">
                            <img src="{{company_logo}}" alt="{{app_name}}" style="max-width: 180px; height: auto; margin-bottom: 20px;" />
                            <h1 style="margin: 0; color: #ffffff; font-size: 28px; font-weight: 600; text-shadow: 0 2px 4px rgba(0,0,0,0.1);">🎊 Order Delivered!</h1>
                            <p style="margin: 10px 0 0; color: #ffffff; font-size: 16px; opacity: 0.95;">Enjoy your meal</p>
                        </td>
                    </tr>
                    
                    <!-- Content -->
                    <tr>
                        <td style="padding: 40px 30px;">
                            <p style="margin: 0 0 10px; color: #333333; font-size: 16px; line-height: 1.6;">Dear {{user_name}},</p>
                            <p style="margin: 0 0 30px; color: #555555; font-size: 15px; line-height: 1.6;">Your order has been successfully delivered! We hope you enjoy every bite. 🍽️</p>
                            
                            <div style="background-color: #e8f5e9; border-left: 4px solid #4caf50; padding: 20px; margin-bottom: 30px; border-radius: 4px; text-align: center;">
                                <h2 style="margin: 0 0 10px; color: #2e7d32; font-size: 24px; font-weight: 700;">✓ Delivered Successfully</h2>
                                <p style="margin: 0; color: #2e7d32; font-size: 14px;">Order #{{order_number}}</p>
                                <p style="margin: 10px 0 0; color: #666666; font-size: 13px;">Delivered on {{delivery_date}}</p>
                            </div>
                            
                            <div style="background-color: #fff3e0; border-left: 4px solid #ff9800; padding: 20px; margin-bottom: 30px; border-radius: 4px;">
                                <h3 style="margin: 0 0 15px; color: #e65100; font-size: 18px; font-weight: 600;">⭐ Rate Your Experience</h3>
                                <p style="margin: 0 0 15px; color: #555555; font-size: 14px; line-height: 1.6;">How was your experience? Your feedback helps us improve and helps other customers make better choices.</p>
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                                    <tr>
                                        <td style="text-align: center; padding: 10px 0;">
                                            <a href="{{review_link}}" style="background-color: #ff9800; color: #ffffff; padding: 12px 30px; text-decoration: none; border-radius: 50px; display: inline-block; font-weight: 600; font-size: 14px;">Write a Review</a>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            
                            <!-- CTA Button -->
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                                <tr>
                                    <td style="text-align: center; padding: 20px 0;">
                                        <a href="{{order_link}}" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); color: #ffffff; padding: 16px 40px; text-decoration: none; border-radius: 50px; display: inline-block; font-weight: 600; font-size: 16px; box-shadow: 0 4px 15px rgba(17, 153, 142, 0.4);">View Order Details</a>
                                    </td>
                                </tr>
                            </table>
                            
                            <div style="background-color: #f8f9fa; padding: 20px; border-radius: 8px; margin-top: 20px; text-align: center;">
                                <p style="margin: 0 0 10px; color: #333333; font-size: 14px; font-weight: 600;">Thank you for choosing {{app_name}}!</p>
                                <p style="margin: 0; color: #666666; font-size: 13px;">We look forward to serving you again soon.</p>
                            </div>
                        </td>
                    </tr>
                    
                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f8f9fa; padding: 30px; text-align: center; border-radius: 0 0 12px 12px; border-top: 1px solid #e9ecef;">
                            <p style="margin: 0 0 10px; color: #666666; font-size: 14px;">Best regards,<br><strong>{{app_name}} Team</strong></p>
                            <p style="margin: 0; color: #999999; font-size: 12px;">&copy; {{current_year}} {{app_name}}. All rights reserved.</p>
                            <p style="margin: 10px 0 0; color: #999999; font-size: 12px;">
                                <a href="{{support_url}}" style="color: #667eea; text-decoration: none;">Support</a> | 
                                <a href="{{privacy_url}}" style="color: #667eea; text-decoration: none;">Privacy Policy</a>
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;
    }

    private function getOrderCancelledEmailTemplate(): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Cancelled</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f4f7fa; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">
    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="background-color: #f4f7fa;">
        <tr>
            <td style="padding: 40px 20px;">
                <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                    <!-- Header with Logo -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); padding: 40px 30px; text-align: center; border-radius: 12px 12px 0 0;">
                            <img src="{{company_logo}}" alt="{{app_name}}" style="max-width: 180px; height: auto; margin-bottom: 20px;" />
                            <h1 style="margin: 0; color: #ffffff; font-size: 28px; font-weight: 600; text-shadow: 0 2px 4px rgba(0,0,0,0.1);">Order Cancelled</h1>
                        </td>
                    </tr>
                    
                    <!-- Content -->
                    <tr>
                        <td style="padding: 40px 30px;">
                            <p style="margin: 0 0 10px; color: #333333; font-size: 16px; line-height: 1.6;">Dear {{user_name}},</p>
                            <p style="margin: 0 0 30px; color: #555555; font-size: 15px; line-height: 1.6;">We're sorry to inform you that your order has been cancelled.</p>
                            
                            <div style="background-color: #fff3cd; border-left: 4px solid #ffc107; padding: 20px; margin-bottom: 30px; border-radius: 4px;">
                                <h2 style="margin: 0 0 20px; color: #856404; font-size: 20px; font-weight: 600;">Cancellation Details</h2>
                                
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="border-collapse: collapse;">
                                    <tr>
                                        <td style="padding: 12px 0; border-bottom: 1px solid #f5e6a8; color: #856404; font-size: 14px; width: 40%;"><strong>Order Number:</strong></td>
                                        <td style="padding: 12px 0; border-bottom: 1px solid #f5e6a8; color: #333333; font-size: 14px;">{{order_number}}</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 12px 0; border-bottom: 1px solid #f5e6a8; color: #856404; font-size: 14px;"><strong>Cancelled Date:</strong></td>
                                        <td style="padding: 12px 0; border-bottom: 1px solid #f5e6a8; color: #333333; font-size: 14px;">{{cancelled_date}}</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 12px 0; border-bottom: 1px solid #f5e6a8; color: #856404; font-size: 14px;"><strong>Order Total:</strong></td>
                                        <td style="padding: 12px 0; border-bottom: 1px solid #f5e6a8; color: #333333; font-size: 14px;">{{order_total}}</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 12px 0; color: #856404; font-size: 14px;" colspan="2"><strong>Reason:</strong></td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 12px 0; color: #333333; font-size: 14px; line-height: 1.6;" colspan="2">{{cancellation_reason}}</td>
                                    </tr>
                                </table>
                            </div>
                            
                            <div style="background-color: #e7f3ff; border-left: 4px solid #2196F3; padding: 20px; margin-bottom: 30px; border-radius: 4px;">
                                <h3 style="margin: 0 0 10px; color: #1976d2; font-size: 18px; font-weight: 600;">💰 Refund Information</h3>
                                <p style="margin: 0; color: #555555; font-size: 14px; line-height: 1.6;">If you've already paid for this order, your refund will be processed within <strong>{{refund_processing_days}} business days</strong>. The amount will be credited back to your original payment method.</p>
                            </div>
                            
                            <!-- CTA Buttons -->
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                                <tr>
                                    <td style="text-align: center; padding: 20px 0;">
                                        <a href="{{browse_menu_link}}" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #ffffff; padding: 16px 40px; text-decoration: none; border-radius: 50px; display: inline-block; font-weight: 600; font-size: 16px; box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4); margin: 0 10px;">Browse Menu</a>
                                        <a href="{{support_url}}" style="background-color: #ffffff; color: #667eea; padding: 16px 40px; text-decoration: none; border-radius: 50px; display: inline-block; font-weight: 600; font-size: 16px; border: 2px solid #667eea; margin: 0 10px;">Contact Support</a>
                                    </td>
                                </tr>
                            </table>
                            
                            <div style="background-color: #f8f9fa; padding: 15px; border-radius: 8px; margin-top: 20px; text-align: center;">
                                <p style="margin: 0; color: #666666; font-size: 13px;">We're sorry for any inconvenience. We'd love to serve you again soon!</p>
                            </div>
                        </td>
                    </tr>
                    
                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f8f9fa; padding: 30px; text-align: center; border-radius: 0 0 12px 12px; border-top: 1px solid #e9ecef;">
                            <p style="margin: 0 0 10px; color: #666666; font-size: 14px;">Best regards,<br><strong>{{app_name}} Team</strong></p>
                            <p style="margin: 0; color: #999999; font-size: 12px;">&copy; {{current_year}} {{app_name}}. All rights reserved.</p>
                            <p style="margin: 10px 0 0; color: #999999; font-size: 12px;">
                                <a href="{{support_url}}" style="color: #667eea; text-decoration: none;">Support</a> | 
                                <a href="{{privacy_url}}" style="color: #667eea; text-decoration: none;">Privacy Policy</a>
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;
    }
}
