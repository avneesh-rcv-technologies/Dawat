<?php

namespace Modules\Orders\Notifications;

use Exception;
use Illuminate\Support\Facades\Log;
use Modules\Orders\Models\Order;
use Modules\Payments\Enums\PaymentStatus;
use Modules\Notifications\Contracts\Notification;
use Modules\Notifications\Models\NotificationEvent;
use Modules\Notifications\Models\NotificationChannel;
use Modules\Notifications\Models\NotificationMapping;

class OrderCreatedNotification implements Notification
{
    public function __construct(protected $order = null, protected $data = []) {}

    public function getEventCode(): string
    {
        return 'order_created';
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

    public function getVariables(): array
    {
  
        \Log::info('vendor_debug', [
            'vendor' => $this->order->vendor?->name,
            'vendor_admin' => $this->order->vendor?->admin?->email,
            'vendor_email' => ($this->order->vendor && $this->order->vendor->admin)
                ? $this->order->vendor->admin->email
                : 'NULL - no admin linked'
        ]);

        // Handle case when called without proper order data
        if (!$this->order instanceof Order) {
            return [];
        }

        $customer = $this->order->customer;
        $vendor = $this->order->vendor;
        $currencyCode = fn_get_setting('general.currency') ?? 'USD';

        // Get order items as array for template
        $items = $this->order->items()->get();
        $itemsArray = [];

        foreach ($items as $item) {
            $itemsArray[] = [
                'item_name' => (string)($item->item_name ?? 'Item'),
                'quantity' => (int)$item->quantity,
                'price' => (string)fn_format_currency_amount($item->total_price, $currencyCode),
                'unit_price' => (string)fn_format_currency_amount($item->price, $currencyCode),
            ];
        }

        // Format addresses as objects - ensure they are arrays
        $billingAddr = is_array($this->order->billing_address) ? $this->order->billing_address : ($this->order->delivery_address ?? []);
        $shippingAddr = is_array($this->order->delivery_address) ? $this->order->delivery_address : [];

        return [
            "customer_id" => $customer->id ?? null,
            'customer_name' => (string)($customer->name ?? 'Guest'),
            'user_name' => (string)($customer->name ?? 'Guest'),
            'customer_email' => (string)($customer->email ?? ''),
            'order_id' => (int)$this->order->id,
            'order_number' => (string)$this->order->order_number,
            'order_date' => (string)$this->order->created_at->format('M d, Y'),
            'order_total' => (string)fn_format_currency_amount($this->order->total, $currencyCode),
            'order_subtotal' => (string)fn_format_currency_amount($this->order->subtotal, $currencyCode),
            'order_discount' => (string)fn_format_currency_amount($this->order->discount ?? 0, $currencyCode),
            'order_tax' => (string)fn_format_currency_amount($this->order->tax, $currencyCode),
            'delivery_fee' => (string)fn_format_currency_amount($this->order->delivery_fee, $currencyCode),
            'order_type' => (string)ucfirst($this->order->order_type ?? 'delivery'),
            'order_status' => (string)$this->order->status,
            'payment_method' => (string)($this->order->payment_method ?? 'N/A'),
            'payment_status' => PaymentStatus::tryFrom($this->order->payment_status ?? PaymentStatus::PENDING->value)?->label()
                ?? \Illuminate\Support\Str::headline($this->order->payment_status ?? 'Unknown'),
            'delivery_address' => (string)$this->formatAddress($this->order->delivery_address),
            'estimated_delivery_time' => (string)($this->order->estimated_delivery_time?->format('Y-m-d H:i:s') ?? 'N/A'),
            'vendor_name' => (string)($vendor->name ?? 'Restaurant'),
            'vendor_email' => (string)(($vendor && $vendor->admin) ? $vendor->admin->email : ''),
            'items_count' => (int)$items->count(),
            'order_items' => (string)$this->formatOrderItems(),
            'vendor_email' => (string)(($vendor && $vendor->admin) ? $vendor->admin->email : ''),
            'items_count' => (int)$items->count(),
            'order_items' => (string)$this->formatOrderItems(),
            'phone_number' => (string)($this->order->phone_number ?? ''),
            'notes' => (string)($this->order->notes ?? ''),

            // Flattened address fields (no nested arrays)
            'billing_name' => (string)($billingAddr['name'] ?? $billingAddr['full_name'] ?? $customer->name ?? 'Guest'),
            'billing_street' => (string)($billingAddr['street'] ?? $billingAddr['address'] ?? $billingAddr['address_line_1'] ?? $billingAddr['address_line_2'] ?? ''),
            'billing_city' => (string)($billingAddr['city'] ?? $billingAddr['city_name'] ?? ''),
            'billing_state' => (string)($billingAddr['state'] ?? $billingAddr['state_id'] ?? $billingAddr['province'] ?? ''),
            'billing_zip' => (string)($billingAddr['postal_code'] ?? $billingAddr['zip'] ?? $billingAddr['zip_code'] ?? $billingAddr['zipcode'] ?? ''),
            'billing_country' => (string)($billingAddr['country'] ?? $billingAddr['country_id'] ?? ''),
            'shipping_name' => (string)($shippingAddr['name'] ?? $shippingAddr['full_name'] ?? $customer->name ?? 'Guest'),
            'shipping_street' => (string)($shippingAddr['street'] ?? $shippingAddr['address'] ?? $shippingAddr['address_line_1'] ?? $shippingAddr['address_line_2'] ?? ''),
            'shipping_city' => (string)($shippingAddr['city'] ?? $shippingAddr['city_name'] ?? ''),
            'shipping_state' => (string)($shippingAddr['state'] ?? $shippingAddr['state_id'] ?? $shippingAddr['province'] ?? ''),
            'shipping_zip' => (string)($shippingAddr['postal_code'] ?? $shippingAddr['zip'] ?? $shippingAddr['zip_code'] ?? $shippingAddr['zipcode'] ?? ''),
            'shipping_country' => (string)($shippingAddr['country'] ?? $shippingAddr['country_id'] ?? ''),

            // System variables
            'order_link' => (string)url('/customer/orders/' . $this->order->id),
            'company_logo' => (string)asset(config('app.logo', 'assets/images/logo.png')),
            'app_name' => (string)config('app.name', 'Food Delivery'),
            'current_year' => (string)date('Y'),
            'support_url' => (string)url('/support'),
            'privacy_url' => (string)url('/privacy'),
        ];
    }

    public function getAvailableVariables(): array
    {
        return [
            'customer_name' => 'Full name of the customer',
            'customer_email' => 'Customer email address',
            'order_id' => 'Unique ID of the order',
            'order_number' => 'Order number for reference',
            'order_date' => 'Date and time when order was created',
            'order_total' => 'Formatted total order amount',
            'order_subtotal' => 'Subtotal before tax and fees',
            'order_discount' => 'Discount amount applied',
            'order_tax' => 'Tax amount',
            'delivery_fee' => 'Delivery fee amount',
            'order_type' => 'Order type (Delivery, Takeaway, Dine In)',
            'order_status' => 'Current order status',
            'payment_method' => 'Payment method used',
            'payment_status' => 'Payment status (Pending, Paid, Failed)',
            'delivery_address' => 'Formatted delivery address',
            'estimated_delivery_time' => 'Estimated delivery time',
            'vendor_name' => 'Restaurant/Vendor name',
            'vendor_email' => 'Restaurant/Vendor email',
            'items_count' => 'Number of items in order',
            'order_items' => 'List of ordered items',
            'phone_number' => 'Customer phone number',
            'notes' => 'Special notes or instructions',
            'order_confirmation_message' => 'Order confirmation message',
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
     * Get order confirmation message
     */
    private function getOrderConfirmationMessage(): string
    {
        return 'Thank you for your order! Your order has been received and is being processed. We will notify you when your order is confirmed by the restaurant.';
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

    /**
     * Format order items for display
     */
    private function formatOrderItems(): string
    {
        if (!$this->order) {
            return '';
        }

        $items = $this->order->items()->get();
        $formatted = [];
        $currencyCode = fn_get_setting('general.currency') ?? 'USD';

        foreach ($items as $item) {
            $formatted[] = sprintf(
                '%s x%d - %s',
                $item->item_name ?? 'Item',
                $item->quantity,
                fn_format_currency_amount($item->total_price, $currencyCode)
            );
        }

        return implode("\n", $formatted);
    }
}
