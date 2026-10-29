<?php

namespace Modules\Acl\Notifications;

use Exception;
use Modules\Acl\Models\Customer;
use Modules\Notifications\Contracts\Notification;
use Modules\Notifications\Models\NotificationEvent;
use Modules\Notifications\Models\NotificationChannel;
use Modules\Notifications\Models\NotificationMapping;

class CustomerPasswordResetNotification implements Notification
{
    public function __construct(protected $customer = null, protected $data = [])
    {
    }

    public function getEventCode(): string
    {
        return 'customer_password_reset';
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
            'reset_time' => now()->format('Y-m-d H:i:s'),
            'login_url' => config('app.url', 'https://app.local') . '/login',
            'app_name' => config('app.name', 'App'),
            'current_year' => now()->year,
            'support_url' => config('app.url', 'https://app.local') . '/support',
            'privacy_url' => config('app.url', 'https://app.local') . '/privacy-policy',
        ];
    }

    public function getAvailableVariables(): array
    {
        return [
            'customer_name' => 'Full name of the customer',
            'customer_email' => 'Customer email address',
            'customer_phone' => 'Customer phone number',
            'customer_id' => 'Unique ID of the customer',
            'reset_time' => 'Date and time when the password was reset',
            'login_url' => 'Customer login URL',
            'app_name' => 'Application name',
            'current_year' => 'Current year',
            'support_url' => 'Support page URL',
            'privacy_url' => 'Privacy policy URL',
        ];
    }

    public function getAvailableRecipients(): array
    {
        return [
            'notify_customer' => 'Notify Customer',
        ];
    }

    public function getRecipientTypeMapping(): array
    {
        return [
            'notify_customer' => 'notifiable',
        ];
    }

    public function getRecipientClassMapping(): array
    {
        return [
            'notify_customer' => \Modules\Acl\Models\Customer::class,
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
