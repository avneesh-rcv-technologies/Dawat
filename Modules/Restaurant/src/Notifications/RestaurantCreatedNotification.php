<?php

namespace Modules\Restaurant\Notifications;

use Exception;
use Modules\Restaurant\Models\Restaurant;
use Modules\Notifications\Contracts\Notification;
use Modules\Notifications\Models\NotificationEvent;
use Modules\Notifications\Models\NotificationChannel;
use Modules\Notifications\Models\NotificationMapping;

class RestaurantCreatedNotification implements Notification
{
    public function __construct(protected $restaurant = null, protected $data = [])
    {
        \Log::info('data', $data);
    }

    public function getEventCode(): string
    {
        return 'restaurant_created';
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

    // public function getVariables(): array
    // {
    //     // Handle case when called without proper restaurant data
    //     if (!$this->restaurant instanceof Restaurant && !isset($this->data['restaurant'])) {
    //         return [];
    //     }

    //     $restaurant = $this->restaurant instanceof Restaurant 
    //         ? $this->restaurant 
    //         : $this->data['restaurant'];

    //     $admin = $restaurant->admin;
    //     $password = $this->data['password'] ?? '********';

    //     return [
    //         'company_logo' =>  fn_get_image('company_logo', 0)['url'] ?? '',
    //         'restaurant_name' => $restaurant->name ?? 'Restaurant',
    //         'restaurant_owner_name' => $admin->name ?? 'Owner',
    //         'restaurant_email' => $admin->email ?? '',
    //         'restaurant_password' => $password,
    //         'restaurant_phone' => $restaurant->phone ?? '',
    //         'restaurant_id' => $restaurant->id,
    //         'created_date' => $restaurant->created_at->format('Y-m-d H:i:s'),
    //         'dashboard_link' => config('app.url', 'https://app.local') . '/dashboard',
    //         'login_url' => config('app.url', 'https://app.local') . '/admin/login',
    //         'app_name' => fn_get_setting('general.company.name'),
    //         'current_year' => date('Y'),
    //         'support_url' => config('app.url', 'https://app.local') . '/support',
    //         'privacy_url' => config('app.url', 'https://app.local') . '/privacy-policy',
    //     ];
    // }

    public function getVariables(): array
    {
        $restaurant = $this->restaurant instanceof Restaurant
            ? $this->restaurant
            : ($this->data['restaurant'] ?? ($this->restaurant['restaurant'] ?? null));

        $password = $this->data['password']
            ?? $this->restaurant['password']
            ?? '********';

        if (!$restaurant instanceof Restaurant) {
            return [];
        }

        $admin = $restaurant->admin;

        return [
            'company_logo'           => fn_get_image('company_logo', 0)['url'] ?? '',
            'restaurant_name'        => $restaurant->name ?? 'Restaurant',
            'restaurant_owner_name'  => $admin->name ?? 'Owner',
            'restaurant_email'       => $admin->email ?? '',
            'restaurant_password'    => $password,
            'restaurant_phone'       => $restaurant->phone ?? '',
            'restaurant_id'          => $restaurant->id,
            'created_date'           => $restaurant->created_at->format('Y-m-d H:i:s'),
            'dashboard_link'         => config('app.url', 'https://app.local') . '/dashboard',
            'login_url'              => config('app.url', 'https://app.local') . '/admin/login',
            'app_name'               => fn_get_setting('general.company.name'),
            'current_year'           => date('Y'),
            'support_url'            => config('app.url', 'https://app.local') . '/support',
            'privacy_url'            => config('app.url', 'https://app.local') . '/privacy-policy',
        ];
    }


    public function getAvailableVariables(): array
    {
        return [
            'restaurant_name' => 'Name of the restaurant',
            'restaurant_owner_name' => 'Name of the restaurant owner',
            'restaurant_email' => 'Restaurant owner email address',
            'restaurant_password' => 'Password for restaurant login',
            'restaurant_phone' => 'Restaurant phone number',
            'restaurant_id' => 'Unique ID of the restaurant',
            'created_date' => 'Date and time when restaurant was registered',
            'dashboard_link' => 'Link to restaurant dashboard',
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
