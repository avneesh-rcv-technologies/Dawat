<?php

namespace Modules\Restaurant\Notifications;

use Exception;
use Modules\Restaurant\Models\Restaurant;
use Modules\Notifications\Contracts\Notification;
use Modules\Notifications\Models\NotificationEvent;
use Modules\Notifications\Models\NotificationChannel;
use Modules\Notifications\Models\NotificationMapping;

class RestaurantOrderReceivedNotification implements Notification
{
    public function __construct(protected $restaurant = null, protected $data = [])
    {
    }

    public function getEventCode(): string
    {
        return 'restaurant_order_received';
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
        // Handle case when called without proper restaurant data
        if (!$this->restaurant instanceof Restaurant) {
            return [];
        }

        $admin = $this->restaurant->admin;
        $order = $this->data['order'] ?? null;

        return [
            'restaurant_name' => $this->restaurant->name ?? 'Restaurant',
            'restaurant_owner_name' => $admin->name ?? 'Owner',
            'restaurant_email' => $admin->email ?? '',
            'restaurant_phone' => $this->restaurant->phone ?? '',
            'restaurant_id' => $this->restaurant->id,
            'order_number' => $order?->order_number ?? 'N/A',
            'order_id' => $order?->id ?? 'N/A',
            'customer_name' => $order?->customer?->name ?? 'Customer',
            'order_total' => $order ? fn_get_currency($order->total) : '0.00',
            'delivery_address' => $this->formatAddress($order?->delivery_address),
            'order_link' => config('app.url', 'https://app.local') . '/orders/' . ($order?->id ?? ''),
            'app_name' => fn_get_setting('general.company.name'),
        ];
    }

    public function getAvailableVariables(): array
    {
        return [
            'restaurant_name' => 'Name of the restaurant',
            'restaurant_owner_name' => 'Name of the restaurant owner',
            'restaurant_email' => 'Restaurant owner email address',
            'restaurant_phone' => 'Restaurant phone number',
            'restaurant_id' => 'Unique ID of the restaurant',
            'order_number' => 'Order number for reference',
            'order_id' => 'Unique ID of the order',
            'customer_name' => 'Name of the customer',
            'order_total' => 'Formatted total order amount',
            'delivery_address' => 'Formatted delivery address',
            'order_link' => 'Link to order details',
            'app_name' => 'Application name',
        ];
    }

    public function getAvailableRecipients(): array
    {
        return [
            'notify_restaurant' => 'Notify Restaurant',
        ];
    }

    public function getRecipientTypeMapping(): array
    {
        return [
            'notify_restaurant' => 'notifiable',
        ];
    }

    public function getRecipientClassMapping(): array
    {
        return [
            'notify_restaurant' => \Modules\Acl\Models\Admin::class,
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
