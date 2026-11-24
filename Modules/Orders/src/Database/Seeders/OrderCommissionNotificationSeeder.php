<?php

namespace Modules\Orders\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Modules\Core\Events\RegisterMenu;
use Modules\Core\Events\RegisterPermissions;
use Modules\EmailNotification\Channels\EmailChannel;
use Modules\EmailNotification\Models\NotificationTemplate;
use Modules\EmailNotification\Support\DawatEmail;
use Modules\Notifications\Models\NotificationChannel;
use Modules\Notifications\Models\NotificationEvent;
use Modules\Notifications\Models\NotificationMapping;
use Modules\Orders\Notifications\OrderCommissionNotification;

class OrderCommissionNotificationSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $event = NotificationEvent::updateOrCreate(['event_code' => 'order_commission_updated'], [
                'event_name' => 'Order Commission Recorded / Status Updated',
                'event_description' => 'Notifies the restaurant owner and platform admin about per-order commission records and processing changes.',
                'notification_class' => OrderCommissionNotification::class,
            ]);
            $template = NotificationTemplate::updateOrCreate(['name' => 'order_commission_updated', 'locale' => 'en'], [
                'subject' => 'Commission {{commission_status}} — {{order_number}}',
                'content' => DawatEmail::render([
                    'title' => 'Order commission — Dawat', 'preheader' => '{{commission_message}}',
                    'badge' => 'ORDER COMMISSION', 'headline' => 'Commission {{commission_status}}',
                    'greeting' => 'Hello,', 'intro' => '{{commission_message}}',
                    'blocks' => [DawatEmail::details('Commission record', [
                        'Order number' => '{{order_number}}', 'Restaurant' => '{{restaurant_name}}',
                        'Order amount' => '{{order_amount}}', 'Accepted rate' => '{{commission_percentage}}%',
                        'Platform commission' => '{{commission_amount}}', 'Restaurant share' => '{{restaurant_amount}}',
                        'Status' => '{{commission_status}}', 'Processed at' => '{{processed_at}}',
                    ])],
                    'buttons' => [DawatEmail::button('View commission record', '{{commission_link}}')],
                    'footer' => 'Commission accounting for a paid Dawat order.',
                ]), 'status' => true,
            ]);
            $channel = NotificationChannel::firstOrCreate(['name' => 'email'], [
                'channel_class' => EmailChannel::class, 'status' => true, 'config' => [],
            ]);
            NotificationMapping::updateOrCreate(['event_id' => $event->id, 'channel_id' => $channel->id], [
                'template_id' => (string) $template->id,
                'recipients' => ['notify_restaurant' => true, 'notify_admin' => true],
            ]);
        });
        RegisterMenu::clearCache();
        RegisterPermissions::clearCache();
        $this->command?->info('Commission emails configured for the restaurant owner and platform admin.');
    }
}
