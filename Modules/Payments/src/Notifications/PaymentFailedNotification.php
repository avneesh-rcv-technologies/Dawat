<?php

namespace Modules\Payments\Notifications;

use Exception;
use Modules\Payments\Models\Payments;
use Modules\Notifications\Contracts\Notification;
use Modules\Notifications\Models\NotificationEvent;
use Modules\Notifications\Models\NotificationChannel;
use Modules\Notifications\Models\NotificationMapping;

class PaymentFailedNotification implements Notification
{
    public function __construct(protected $payment = null, protected $data = [])
    {
    }

    public function getEventCode(): string
    {
        return 'payment_failed';
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
        // Handle case when called without proper payment data
        if (!$this->payment instanceof Payments) {
            return [];
        }

        $order = $this->payment->order;
        $customer = $order?->customer;
        $currencyCode = $this->payment->currency ?? config('app.currency', 'USD');

        return [
            'customer_name' => $customer?->name ?? 'Customer',
            'customer_email' => $customer?->email ?? '',
            'order_number' => $order?->order_number ?? 'N/A',
            'order_id' => $order?->id ?? 'N/A',
            'payment_amount' => fn_format_currency_amount($this->payment->amount, $currencyCode),
            'payment_method' => $this->payment->method?->name ?? 'N/A',
            'failure_reason' => $this->payment->failure_reason ?? 'Payment processing failed',
            'payment_id' => $this->payment->id,
            'retry_link' => config('app.url', 'https://app.local') . '/orders/' . ($order?->id ?? '') . '/retry-payment',
            'app_name' => config('app.name', 'App'),
        ];
    }

    public function getAvailableVariables(): array
    {
        return [
            'customer_name' => 'Full name of the customer',
            'customer_email' => 'Customer email address',
            'order_number' => 'Order number for reference',
            'order_id' => 'Unique ID of the order',
            'payment_amount' => 'Formatted payment amount',
            'payment_method' => 'Payment method used',
            'failure_reason' => 'Reason for payment failure',
            'payment_id' => 'Unique ID of the payment',
            'retry_link' => 'Link to retry payment',
            'app_name' => 'Application name',
        ];
    }

    public function getAvailableRecipients(): array
    {
        return [
            'notify_customer' => 'Notify Customer',
            'notify_admin' => 'Notify Admin',
        ];
    }

    public function getRecipientTypeMapping(): array
    {
        return [
            'notify_customer' => 'notifiable',
            'notify_admin' => 'admin',
        ];
    }

    public function getRecipientClassMapping(): array
    {
        return [
            'notify_customer' => \Modules\Acl\Models\Customer::class,
            'notify_admin' => \Modules\Acl\Models\Admin::class,
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
}
