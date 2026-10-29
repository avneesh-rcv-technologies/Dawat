<?php

namespace Modules\Acl\Notifications;

use Exception;
use Modules\Acl\Models\Customer;
use Modules\Notifications\Contracts\Notification;
use Modules\Notifications\Models\NotificationEvent;
use Modules\Notifications\Models\NotificationChannel;
use Modules\Notifications\Models\NotificationMapping;

class CustomerCreatedNotification implements Notification
{
    public function __construct(protected $customer = null, protected $data = [])
    {
    }

    public function getEventCode(): string
    {
        return 'customer_created';
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
        // Handle case when called without proper customer data
        if (!$this->customer instanceof Customer) {
            return [];
        }

        return [
            'customer_name' => $this->customer->name ?? 'Customer',
            'customer_email' => $this->customer->email ?? '',
            'customer_phone' => $this->customer->phone ?? '',
            'customer_id' => $this->customer->id,
            'created_date' => $this->customer->created_at->format('Y-m-d H:i:s'),
            'app_name' => config('app.name', 'App'),
            'app_url' => config('app.url', 'https://app.local'),
        ];
    }

    public function getAvailableVariables(): array
    {
        return [
            'customer_name' => 'Full name of the customer',
            'customer_email' => 'Customer email address',
            'customer_phone' => 'Customer phone number',
            'customer_id' => 'Unique ID of the customer',
            'created_date' => 'Date and time when account was created',
            'app_name' => 'Application name',
            'app_url' => 'Application URL',
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
