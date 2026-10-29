<?php

namespace Modules\Restaurant\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\EmailNotification\Models\NotificationTemplate as EmailTemplate;
use Modules\Notifications\Models\NotificationChannel;
use Modules\Notifications\Models\NotificationEvent;
use Modules\Notifications\Models\NotificationMapping;
use Modules\Restaurant\Notifications\RestaurantBankingResubmittedNotification;
use Modules\Restaurant\Notifications\RestaurantTaxResubmittedNotification;
use Modules\WebPushNotification\Models\NotificationTemplate as WebPushTemplate;

class RestaurantResubmittedDetailsNotificationSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['banking' => new RestaurantBankingResubmittedNotification(), 'tax' => new RestaurantTaxResubmittedNotification()] as $kind => $notification) {
            $code = $notification->getEventCode();
            $label = ucfirst($kind) . ' Information';
            $event = NotificationEvent::updateOrCreate(['event_code' => $code], [
                'event_name' => $label . ' — Resubmitted Details',
                'event_description' => 'Notifies the owner and admin when ' . $kind . ' details are resubmitted for review.',
            ]);
            $message = $label . ' for {{restaurant_name}} was resubmitted on {{updated_at}}. The step is awaiting a new admin review.';
            $email = EmailTemplate::updateOrCreate(['name' => $code], [
                'subject' => 'Resubmitted Details - ' . $label . ' - {{app_name}}',
                'content' => '<!DOCTYPE html><html lang="en"><body style="font-family:Arial,sans-serif;color:#334155"><h1>Resubmitted Details</h1><p>' . $message . '</p><p>This submission requires another approval or rejection by an administrator.</p><p>{{app_name}} Team</p></body></html>',
                'status' => true, 'locale' => 'en',
            ]);
            $push = WebPushTemplate::updateOrCreate(['name' => $code], [
                'subject' => 'Resubmitted Details - ' . $label,
                'body' => $message,
                'variables' => collect($notification->getAvailableVariables())
                    ->map(fn ($description, $name) => ['name' => $name, 'description' => $description])->values()->all(),
                'is_active' => true,
            ]);
            foreach (['email' => $email, 'webpush' => $push] as $name => $template) {
                $channel = NotificationChannel::where('name', $name)->first();
                if ($channel) {
                    NotificationMapping::updateOrCreate(['event_id' => $event->id, 'channel_id' => $channel->id], [
                        'template_id' => (string) $template->id,
                        'recipients' => ['notify_restaurant' => true, 'notify_admin' => true],
                    ]);
                }
            }
        }
    }
}
