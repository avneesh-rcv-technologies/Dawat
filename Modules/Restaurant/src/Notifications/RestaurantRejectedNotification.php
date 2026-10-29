<?php

namespace Modules\Restaurant\Notifications;

use Exception;
use Modules\Restaurant\Models\Restaurant;
use Modules\Notifications\Contracts\Notification;
use Modules\Notifications\Models\NotificationEvent;
use Modules\Notifications\Models\NotificationChannel;
use Modules\Notifications\Models\NotificationMapping;

class RestaurantRejectedNotification implements Notification
{
    public function __construct(protected $restaurant = null, protected $data = [])
    {
    }

    public function getEventCode(): string
    {
        return 'restaurant_rejected';
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
            'rejected_date' => now()->format('Y-m-d H:i:s'),
            'rejection_reason' => $this->restaurant->rejection_reason ?? 'Not specified',
            'reapply_link' => config('app.url', 'https://app.local') . '/partner-with-us',
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
            'rejected_date' => 'Date and time when restaurant was rejected',
            'rejection_reason' => 'Reason for rejection',
            'reapply_link' => 'Link to reapply for partnership',
            'app_name' => 'Application name',
        ];
    }

    public function getAvailableRecipients(): array
    {
        return [
            'notify_restaurant' => 'Notify Restaurant',
            'notify_admin' => 'Notify Admin',
        ];
    }

    public function getRecipientTypeMapping(): array
    {
        return [
            'notify_restaurant' => 'notifiable',
            'notify_admin' => 'admin',
        ];
    }

    public function getRecipientClassMapping(): array
    {
        return [
            'notify_restaurant' => \Modules\Acl\Models\Admin::class,
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
