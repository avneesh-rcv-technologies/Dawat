<?php

namespace Modules\Orders\Notifications;

use Exception;
use Modules\Orders\Models\Order;
use Modules\Payments\Enums\PaymentStatus;
use Modules\Notifications\Contracts\Notification;
use Modules\Notifications\Models\NotificationEvent;
use Modules\Notifications\Models\NotificationChannel;
use Modules\Notifications\Models\NotificationMapping;

class OrderStatusUpdateNotification implements Notification
{
    protected $oldStatus;

    // public function __construct(protected $order = null, protected $data = [])
    // {

    //     $this->oldStatus = $data['old_status'] ?? null;
    // }

    public function __construct(protected $order = null, protected $data = [])
    {

        if (is_array($this->order) && isset($this->order['order'])) {
            $this->data = $this->order;  // Move array to $data
            $this->order = $this->data['order'];  // Extract actual Order object
        }

        $this->oldStatus = $this->data['old_status'] ?? null;

        \Log::info('After Constructor Fix', [
            'order_is_Order' => $this->order instanceof Order,
            'oldStatus' => $this->oldStatus,
            'data_keys' => array_keys($this->data),
        ]);
    }

    public function getEventCode(): string
    {
        return 'order_status_updated';
    }

    public function getTemplateIdentifier(): array
    {
        $channels = [];

        try {
            $event = NotificationEvent::where('event_code', $this->getEventCode())->first();

            if (!$event) {
                return $channels;
            }

            $mappings = NotificationMapping::where('event_id', $event->id)->get();

            foreach ($mappings as $mapping) {
                $channel = NotificationChannel::find($mapping->channel_id);
                if ($channel) {
                    $channels[$channel->name] = $mapping->template_id;

                    // Add recipient flags from mapping
                    if (!empty($mapping->recipients)) {
                        $channels = array_merge($channels, $mapping->recipients);
                    }
                }
            }
        } catch (Exception $e) {
            // Log error if needed
        }

        return $channels;
    }

    // public function getVariables(): array
    // {
    //     $order = $this->data['order'] ?? null;

    //     // Handle case when called without proper order data
    //     if (!$order instanceof Order) {
    //         return [];
    //     }

    //     $customer = $order->customer;
    //     $vendor = $order->vendor;

    //     return [
    //         'customer_name' => $customer->name ?? 'Guest',
    //         'customer_email' => $customer->email ?? '',
    //         'order_id' => $order->id,
    //         'order_number' => $order->order_number,
    //         'old_status' => $this->getStatusLabel($this->oldStatus ?? $this->order->status),
    //         'new_status' => $this->getStatusLabel($this->order->status),
    //         'status_changed_at' => now()->format('Y-m-d H:i:s'),
    //         'order_total' => fn_get_currency($order->total),
    //         'order_type' => ucfirst($order->order_type),
    //         'payment_status' => ucfirst($order->payment_status),
    //         'delivery_address' => $this->formatAddress($order->delivery_address),
    //         'estimated_delivery_time' => $order->estimated_delivery_time?->format('Y-m-d H:i:s') ?? 'N/A',
    //         'vendor_name' => $vendor->name ?? 'Restaurant',
    //         'vendor_email' => $vendor->email ?? '',
    //         'phone_number' => $order->phone_number ?? '',
    //         'status_message' => $this->getStatusMessage(),
    //         'next_action' => $this->getNextAction(),
    //     ];
    // }

    public function getVariables(): array
    {
        \Log::info('getVariables() called', [
            'data_keys' => array_keys($this->data),
            'has_order_in_data' => isset($this->data['order']),
        ]);

        $order = $this->data['order'] ?? null;

        \Log::info('Order extracted', [
            'order_is_null' => is_null($order),
            'order_is_Order' => $order instanceof Order,
            'order_type' => gettype($order),
        ]);

        // Handle case when called without proper order data
        if (!$order instanceof Order) {
            \Log::warning('Order is not an Order instance, returning empty');
            return [];
        }

        $customer = $order->customer;
        $vendor = $order->vendor;
        $currencyCode = fn_get_setting('general.currency') ?? 'USD';

        $variables = [
            "customer_id" => $customer->id ?? null,
            'customer_name' => $customer->name ?? 'Guest',
            'user_name' => $customer->name ?? 'Guest',
            'customer_email' => $customer->email ?? '',
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'old_status' => $this->getStatusLabel($this->oldStatus ?? $order->status),
            'new_status' => $this->getStatusLabel($order->status),
            'status_changed_at' => now()->format('Y-m-d H:i:s'),
            'order_total' => (string)fn_format_currency_amount($order->total, $currencyCode),
            'order_type' => ucfirst($order->order_type),
            'payment_status' => PaymentStatus::tryFrom($order->payment_status ?? '')?->label()
                ?? \Illuminate\Support\Str::headline($order->payment_status ?? 'Unknown'),
            'delivery_address' => $this->formatAddress($order->delivery_address),
            'estimated_delivery_time' => $order->estimated_delivery_time?->format('Y-m-d H:i:s') ?? 'N/A',
            'vendor_name' => $vendor->name ?? 'Restaurant',
            'vendor_email' => $vendor->email ?? '',
            'phone_number' => $order->phone_number ?? '',
            'status_message' => $this->getStatusMessage(),
            'next_action' => $this->getNextAction(),
            "notification_type" => "order_status_update",
            "order_status" => $this->getStatusLabel($order->status),

            // System variables for email template
            'order_link' => (string)url('/customer/orders/' . $order->id),
            'company_logo' => (string)asset(config('app.logo', 'assets/images/logo.png')),
            'app_name' => (string)config('app.name', 'Food Delivery'),
            'current_year' => (string)date('Y'),
            'support_url' => (string)url('/support'),
            'privacy_url' => (string)url('/privacy'),
        ];

        // \Log::info('Variables generated', ['count' => count($variables)]);

        return $variables;
    }


    public function getAvailableVariables(): array
    {
        return [
            'customer_name' => 'Full name of the customer',
            'customer_email' => 'Customer email address',
            'order_id' => 'Unique ID of the order',
            'order_number' => 'Order number for reference',
            'old_status' => 'Previous order status',
            'new_status' => 'New order status',
            'status_changed_at' => 'Date and time when status changed',
            'order_total' => 'Formatted total order amount',
            'order_type' => 'Order type (Delivery, Takeaway, Dine In)',
            'payment_status' => 'Payment status',
            'delivery_address' => 'Formatted delivery address',
            'estimated_delivery_time' => 'Estimated delivery time',
            'vendor_name' => 'Restaurant/Vendor name',
            'vendor_email' => 'Restaurant/Vendor email',
            'phone_number' => 'Customer phone number',
            'status_message' => 'Human-readable status message',
            'next_action' => 'Suggested next action for customer',

        ];
    }

    public function getAvailableRecipients(): array
    {
        return [
            'notify_customer' => 'Notify Customer',
            'notify_admin' => 'Notify Admin',
            'notify_vendor' => 'Notify Vendor/Restaurant',
        ];
    }

    public function getRecipientTypeMapping(): array
    {
        return [
            'notify_customer' => 'notifiable',
            'notify_admin' => 'admin',
            'notify_vendor' => 'notifiable',
        ];
    }

    public function getRecipientClassMapping(): array
    {
        return [
            'notify_customer' => \Modules\Acl\Models\Customer::class,
            'notify_admin' => \Modules\Acl\Models\Admin::class,
            'notify_vendor' => \Modules\Acl\Models\Admin::class,
        ];
    }

    public function toArray(): array
    {
        return [
            'event_code' => $this->getEventCode(),
            'template' => $this->getTemplateIdentifier(),
            'variables' => $this->getVariables(),
            'available_variables' => $this->getAvailableVariables(),
        ];
    }

    /**
     * Get human-readable status label
     */
    private function getStatusLabel($status): string
    {
        $statusLabels = [
            'O' => 'Order Received',
            'N' => 'Out for Delivery',
            'P' => 'Order Accepted',
            'H' => 'Preparing',
            'Z' => 'Ready',
            'F' => 'Failed',
            'R' => 'Refunded',
            'C' => 'Cancelled',
            'D' => 'Delivered',
        ];

        return $statusLabels[$status] ?? ucfirst($status);
    }

    /**
     * Get status-specific message
     */
    private function getStatusMessage(): string
    {
        $messages = [
            'O' => 'Your order has been received and is being processed.',
            'N' => 'Your order is out for delivery.',
            'P' => 'Your order is pending confirmation from the restaurant.',
            'H' => 'Your order is being prepared by the restaurant.',
            'Z' => 'Your order is ready for pickup.',
            'D' => 'Your order has been delivered. Enjoy your meal!',
            'F' => 'Your order could not be processed. Please contact support.',
            'R' => 'Your order has been refunded.',
            'C' => 'Your order has been cancelled.',
        ];
        $order = $this->data['order'] ?? null;
        $status = $order->status;
        return $messages[$status] ?? 'Your order status has been updated.';
    }

    /**
     * Get suggested next action based on status
     */
    private function getNextAction(): string
    {
        $order = $this->data['order'] ?? null;
        $status = $order->status;

        $actions = [
            'O' => 'Wait for restaurant confirmation',
            'N' => 'Complete your order to proceed',
            'P' => 'Your order will be confirmed shortly',
            'H' => 'Your order is being prepared',
            'Z' => 'Order completed. Rate your experience',
            'F' => 'Contact support for assistance',
            'R' => 'Check your account for refund details',
            'C' => 'Order cancelled. Place a new order',
        ];

        return $actions[$status] ?? 'Check your order details';
    }

    /**
     * Format delivery address for display
     */
    private function formatAddress($address): string
    {
        if (!is_array($address)) {
            return '';
        }

        $parts = array_filter([
            $address['street'] ?? '',
            $address['city'] ?? '',
            $address['state'] ?? '',
            $address['postal_code'] ?? '',
            $address['country'] ?? '',
        ]);

        return implode(', ', $parts);
    }
}
