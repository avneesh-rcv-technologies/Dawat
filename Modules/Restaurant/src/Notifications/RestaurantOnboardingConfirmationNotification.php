<?php

namespace Modules\Restaurant\Notifications;

use Exception;
use Modules\Notifications\Contracts\Notification;
use Modules\Notifications\Models\NotificationEvent;
use Modules\Notifications\Models\NotificationChannel;
use Modules\Notifications\Models\NotificationMapping;

class RestaurantOnboardingConfirmationNotification implements Notification
{
    public function __construct(protected $data = []) {}

    public function getEventCode(): string
    {
        return 'restaurant_onboarding_confirmation';
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
        if (empty($this->data)) {
            return [];
        }

        return [
            "company_logo" =>  fn_get_image('company_logo', 0)['url'] ?? '',
            'restaurant_name' => $this->data['restaurant_name'] ?? 'Restaurant',
            'restaurant_owner_name' => $this->data['restaurant_owner_name'] ?? 'Owner',
            'restaurant_id' => $this->data['restaurant_id'] ?? '',
            'submitted_date' => $this->data['submitted_date'] ?? '',
            'dashboard_link' => config('app.url', 'https://app.local') . '/dashboard',
            'app_name' => fn_get_setting('general.company.name'),
            'current_year' => date('Y'),
            'support_url' => config('app.url', 'https://app.local') . '/support',
            'privacy_url' => config('app.url', 'https://app.local') . '/privacy-policy',
        ];
    }

    public function getAvailableVariables(): array
    {
        return [
            'restaurant_name' => 'Name of the restaurant',
            'restaurant_owner_name' => 'Name of the restaurant owner',
            'restaurant_id' => 'Unique ID of the restaurant',
            'submitted_date' => 'Date and time when form was submitted',
            'dashboard_link' => 'Link to restaurant dashboard',
            'app_name' => 'Application name',
            'current_year' => 'Current year',
            'support_url' => 'Support page URL',
            'privacy_url' => 'Privacy policy URL',
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
