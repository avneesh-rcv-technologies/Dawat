<?php

namespace Modules\Restaurant\Notifications;

use Exception;
use Modules\Restaurant\Models\Restaurant;
use Modules\Notifications\Contracts\Notification;
use Modules\Notifications\Models\NotificationEvent;
use Modules\Notifications\Models\NotificationChannel;
use Modules\Notifications\Models\NotificationMapping;

class RestaurantStatusChangedNotification implements Notification
{
    public function __construct(protected $restaurant = null, protected $data = [])
    {
    }

    public function getEventCode(): string
    {
        return 'restaurant_status_changed';
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

        return [
            "company_logo" =>  fn_get_image('company_logo', 0)['url'] ?? '',
            'restaurant_name' => $this->restaurant->name ?? 'Restaurant',
            'restaurant_owner_name' => $admin->name ?? 'Owner',
            'restaurant_email' => $admin->email ?? '',
            'restaurant_phone' => $this->restaurant->phone ?? '',
            'restaurant_id' => $this->restaurant->id,
            'restaurant_status' => $this->restaurant->status ?? 'active',
            'updated_date' => now()->format('Y-m-d H:i:s'),
            'dashboard_link' => config('app.url', 'https://app.local') . '/dashboard',
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
            'restaurant_status' => 'Current status of the restaurant',
            'updated_date' => 'Date and time when status was changed',
            'dashboard_link' => 'Link to restaurant dashboard',
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
}
