<?php

namespace Modules\Restaurant\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\EmailNotification\Support\SeederEmailView;
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
                'notification_class' => get_class($notification),
                'event_name' => $label . ' — Resubmitted Details',
                'event_description' => 'Notifies the owner and admin when ' . $kind . ' details are resubmitted for review.',
            ]);
            $message = $label . ' for {{restaurant_name}} was resubmitted on {{updated_at}}. The step is awaiting a new admin review.';
            $email = $kind === 'banking' ? $this->seedBankingEmailTemplate()
                : (new RestaurantTaxNotificationSeeder())->seedEmailTemplate('resubmitted');
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

    public function seedBankingEmailTemplate(): EmailTemplate
    {
        return EmailTemplate::updateOrCreate(['name' => 'restaurant_banking_resubmitted', 'locale' => 'en'], [
            'subject' => 'Banking information resubmitted — Dawat',
            'content' => SeederEmailView::make('restaurant::emails.banking-submitted', [
                'resubmitted' => true,
                'restaurantName' => '{{restaurant_name}}',
                'submittedAt' => '{{submitted_at}}',
                'loginUrl' => '{{login_url}}',
                'supportUrl' => '{{support_url}}',
                'currentYear' => '{{current_year}}',
            ])->render(),
            'status' => true,
        ]);
    }
}
