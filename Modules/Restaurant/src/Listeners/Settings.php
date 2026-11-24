<?php

namespace Modules\Restaurant\Listeners;

use Modules\Core\Events\RegisterSettingsMenu;

class Settings
{
    public function handle(RegisterSettingsMenu $event): void
    {
        foreach ([
            'general-information' => ['General Information', 'Manage restaurant details, contact information, images, and address.', 'info'],
            'dietary-certificates' => ['Dietary Certificates', 'Upload dietary certificates and view their review status and feedback.', 'verified'],
            'preferences' => ['Restaurant Settings', 'Configure restaurant status, food type, services, and order cancellation settings.', 'storefront'],
            'delivery-policy' => ['Order Delivery Policy', 'Manage delivery radius, delivery charges, and free delivery rules.', 'local_shipping'],
            'gallery' => ['Gallery', 'Upload and manage images displayed on your restaurant detail page.', 'photo_library'],
        ] as $section => [$title, $description, $icon]) {
            $event->register([
                'category_code' => 'general',
                'title' => $title,
                'description' => $description,
                'route' => route("settings.restaurant.{$section}.index"),
                'icon' => $icon,
                'guard' => 'admin',
                'permission' => "settings.restaurant.{$section}.index",
                'owner_only' => true,
            ]);
        }
        $event->register([
            'category_code' => 'general',
            'title' => 'Working Hours',
            'description' => 'Set your restaurant operating days, opening times, and daily intervals.',
            'route' => route('settings.restaurant.working-hours.index'),
            'icon' => 'schedule',
            'guard' => 'admin',
            'permission' => 'settings.restaurant.working-hours.index',
            'owner_only' => true,
        ]);
        $event->register([
            'category_code' => 'general',
            'title' => 'Holiday Management',
            'description' => 'Manage holiday dates and reasons for restaurant closures.',
            'route' => route('settings.restaurant.holidays.index'),
            'icon' => 'event',
            'guard' => 'admin',
            'permission' => 'settings.restaurant.holidays.index',
            'owner_only' => true,
        ]);
    }
}
