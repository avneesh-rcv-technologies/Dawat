<?php

namespace Modules\Restaurant\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\EmailNotification\Channels\EmailChannel;
use Modules\EmailNotification\Models\NotificationTemplate;
use Modules\EmailNotification\Support\SeederEmailView;
use Modules\Notifications\Models\NotificationChannel;
use Modules\Notifications\Models\NotificationEvent;
use Modules\Notifications\Models\NotificationMapping;
use Modules\Restaurant\Notifications\RestaurantCommissionAcceptedNotification;

class RestaurantCommissionAcceptedNotificationSeeder extends Seeder
{
    public function run(): void
    {
        $event = NotificationEvent::updateOrCreate(['event_code' => RestaurantCommissionAcceptedNotification::EVENT], [
            'event_name' => 'Restaurant Commission Accepted',
            'event_description' => 'Confirms the commission rate and acceptance time after a successful owner banking submission or update.',
            'notification_class' => RestaurantCommissionAcceptedNotification::class,
        ]);
        $template = NotificationTemplate::updateOrCreate(['name' => RestaurantCommissionAcceptedNotification::EVENT, 'locale' => 'en'], [
            'subject' => 'Platform commission accepted — Dawat',
            'content' => SeederEmailView::make('restaurant::emails.commission-accepted', [
                'ownerName' => '{{owner_name}}', 'restaurantName' => '{{restaurant_name}}',
                'commissionPercentage' => '{{commission_percentage}}', 'acceptedAt' => '{{accepted_at}}',
                'agreementText' => '{{agreement_text}}', 'loginUrl' => '{{login_url}}',
                'supportUrl' => '{{support_url}}', 'currentYear' => '{{current_year}}',
            ])->render(),
            'status' => true,
        ]);
        $channel = NotificationChannel::firstOrCreate(['name' => 'email'], [
            'channel_class' => EmailChannel::class, 'status' => true, 'config' => [],
        ]);
        NotificationMapping::firstOrCreate(['event_id' => $event->id, 'channel_id' => $channel->id], [
            'template_id' => (string) $template->id, 'recipients' => ['notify_restaurant' => true],
        ]);
    }
}
