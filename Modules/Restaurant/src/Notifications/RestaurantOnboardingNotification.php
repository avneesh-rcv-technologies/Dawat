<?php

namespace Modules\Restaurant\Notifications;

use Exception;
use Modules\Notifications\Contracts\Notification;
use Modules\Notifications\Models\NotificationEvent;
use Modules\Notifications\Models\NotificationChannel;
use Modules\Notifications\Models\NotificationMapping;

class RestaurantOnboardingNotification implements Notification
{
    public function __construct(protected $restaurant = null, protected $data = []) {}
    public function getEventCode(): string
    {
        return 'restaurant_onboarding';
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
        // NotificationService passes full array as first arg
        $data = is_array($this->restaurant) ? $this->restaurant : $this->data;

        if (empty($data)) {
            return [];
        }

        return [
            'company_logo'          => fn_get_image('company_logo', 0)['url'] ?? '',
            'restaurant_name'       => $data['restaurant_name'] ?? 'Restaurant',
            'restaurant_owner_name' => $data['restaurant_owner_name'] ?? 'Owner',
            'restaurant_email'      => $data['restaurant_email'] ?? '',
            'restaurant_password'   => $data['restaurant_password'] ?? '',
            'restaurant_phone'      => $data['restaurant_phone'] ?? '',
            'restaurant_city'       => $data['restaurant_city'] ?? '',
            'restaurant_address'    => $data['restaurant_address'] ?? '',
            'restaurant_id'         => $data['restaurant_id'] ?? '',
            'submitted_date'        => $data['submitted_date'] ?? '',
            'review_link'           => config('app.url') . '/admin/restaurant/approvals/' . ($data['restaurant_id'] ?? ''),
            'login_url'             => config('app.url') . '/admin/login',
            'app_name'              => fn_get_setting('general.company.name'),
            'current_year'          => date('Y'),
            'support_url'           => config('app.url') . '/support',
            'privacy_url'           => config('app.url') . '/privacy-policy',
        ];
    }

    public function getAvailableVariables(): array
    {
        return [
            'restaurant_name' => 'Name of the restaurant',
            'restaurant_owner_name' => 'Name of the restaurant owner',
            'restaurant_email' => 'Restaurant owner email address',
            'restaurant_password' => 'Temporary password for restaurant login',
            'restaurant_phone' => 'Restaurant phone number',
            'restaurant_city' => 'City where restaurant is located',
            'restaurant_address' => 'Restaurant address',
            'restaurant_id' => 'Unique ID of the restaurant',
            'submitted_date' => 'Date and time when form was submitted',
            'review_link' => 'Link to review the restaurant application',
            'login_url' => 'Direct URL to login page',
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
