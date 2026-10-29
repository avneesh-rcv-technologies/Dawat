<?php

namespace Modules\Acl\Notifications\Customer;

use Exception;
use Modules\Notifications\Contracts\Notification;
use Modules\Notifications\Models\NotificationEvent;
use Modules\Notifications\Models\NotificationChannel;
use Modules\Notifications\Models\NotificationMapping;
use Modules\Activitylog\Exceptions\ApiExceptionHandler;

class CustomerUpdateNotification implements Notification
{
    public function __construct(protected $data) {}

    public function getEventCode(): string
    {
        return 'update_customer_account';
    }

    public function getTemplateIdentifier(): array
    {
        $channels = [];
        try {
            // Get the event first
            $event = NotificationEvent::where('event_code', $this->getEventCode())->first();

            if (!$event) {
                return $channels;
            }
            // Get the mapping using the event ID
            $mappings = NotificationMapping::where('event_id', $event->id)
                ->get();
            foreach ($mappings as $key => $mapping) {
                $channel_name = NotificationChannel::where('id', $mapping->channel_id)->first();
                $channels[$channel_name->name] = $mapping->template_id;
                $channels = array_merge($channels, $mapping->recipients);
            }
        } catch (Exception $e) {
        }

        return $channels;
    }

    public function getVariables(): array
    {
        $this->data['login_url'] = route('customer.login');
        return $this->data;
    }

    public function getAvailableVariables(): array
    {
        return [
            'name' => "User name",
            'email' => "User email",
            'phone' => "User phone",
            'password' => "User Random password",
            'login_url' => "Login URL",
        ];
    }

    public function getAvailableRecipients(): array
    {
        return [
            'notify_customer' => 'Notify customer',
            'notify_user_customer' => 'Notify User customer',
        ];
    }
    public function getRecipientTypeMapping(): array
    {
        return [
            'notify_customer' => 'customer',
            'notify_user_customer' => 'notifiable',
        ];
    }

    public function getRecipientClassMapping(): array
    {
        return [
            'notify_customer' => \Modules\Acl\Models\Customer::class,
            'notify_user_customer' => \Modules\Acl\Models\Customer::class
        ];
    }

    public function toArray(): array
    {
        return [
            'event_code' => $this->getEventCode(),
            'template' => $this->getTemplateIdentifier(),
            'variables' => $this->getVariables(),
            'available_variables' => $this->getAvailableVariables()
        ];
    }
}
