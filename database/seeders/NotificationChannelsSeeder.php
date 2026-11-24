<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Notifications\Models\NotificationChannel;

class NotificationChannelsSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['email' => \Modules\EmailNotification\Channels\EmailChannel::class,
            'webpush' => \Modules\WebPushNotification\Channels\WebPushChannel::class] as $name => $handler) {
            NotificationChannel::firstOrCreate(['name' => $name], [
                'channel_class' => $handler, 'status' => true, 'config' => [],
            ]);
        }
    }
}
