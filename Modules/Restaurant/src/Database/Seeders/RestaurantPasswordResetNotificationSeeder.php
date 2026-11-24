<?php

namespace Modules\Restaurant\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\EmailNotification\Support\SeederEmailView;
use Modules\EmailNotification\Models\NotificationTemplate as EmailTemplate;
use Modules\Notifications\Models\NotificationChannel;
use Modules\Notifications\Models\NotificationEvent;
use Modules\Notifications\Models\NotificationMapping;
use Modules\WebPushNotification\Models\NotificationTemplate as WebPushTemplate;
use Modules\Restaurant\Notifications\RestaurantPasswordResetNotification;

class RestaurantPasswordResetNotificationSeeder extends Seeder
{
    public function run(): void
    {
        $passwordResetEvent = NotificationEvent::updateOrCreate(
            ['event_code' => 'restaurant_password_reset'],
            [
                'event_name' => 'Restaurant Password Reset',
                'event_description' => 'Triggered when a restaurant owner resets their password successfully',
                'notification_class' => RestaurantPasswordResetNotification::class,
            ]
        );

        $passwordResetEmailTpl = EmailTemplate::updateOrCreate(
            ['name' => 'restaurant_password_reset', 'locale' => 'en'],
            [
                'subject' => 'Your password has been updated — Dawat',
                'content' => $this->tplRestaurantPasswordReset(),
                'status' => true,
                'locale' => 'en',
            ]
        );

        $passwordResetPushTpl = WebPushTemplate::firstOrCreate(
            ['name' => 'restaurant_password_reset'],
            [
                'subject' => 'Password Updated',
                'body' => 'Your restaurant password was updated successfully on {{reset_time}}.',
                'variables' => [
                    'restaurant_name' => 'Restaurant name',
                    'reset_time' => 'Password reset timestamp',
                ],
                'is_active' => true,
            ]
        );
        $emailChannel = NotificationChannel::where('name', 'email')->first();
        $webpushChannel = NotificationChannel::where('name', 'webpush')->first();
        if ($emailChannel) {
            NotificationMapping::updateOrCreate(
                ['event_id' => $passwordResetEvent->id, 'channel_id' => $emailChannel->id],
                [
                    'template_id' => (string) $passwordResetEmailTpl->id,
                    'recipients' => ['notify_restaurant' => true],
                ]
            );
        }
        if ($webpushChannel) {
            NotificationMapping::updateOrCreate(
                ['event_id' => $passwordResetEvent->id, 'channel_id' => $webpushChannel->id],
                [
                    'template_id' => (string) $passwordResetPushTpl->id,
                    'recipients' => ['notify_restaurant' => true],
                ]
            );
        }
    }

    private function tplRestaurantPasswordReset(): string
    {
        return SeederEmailView::make('restaurant::emails.restaurant-password-reset', [
            'ownerName' => '{{owner_name}}',
            'restaurantName' => '{{restaurant_name}}',
            'accountEmail' => '{{restaurant_email}}',
            'resetTime' => '{{reset_time}}',
            'loginUrl' => '{{login_url}}',
            'forgotPasswordUrl' => '{{forgot_password_url}}',
            'supportUrl' => '{{support_url}}',
            'currentYear' => '{{current_year}}',
        ])->render();
    }
}
