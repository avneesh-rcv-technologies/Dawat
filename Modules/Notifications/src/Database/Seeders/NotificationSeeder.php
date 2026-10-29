<?php

namespace Modules\Notifications\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Notifications\Models\Notification;
use Carbon\Carbon;

class NotificationSeeder extends Seeder
{
    public function run()
    {
        $user = \Modules\Acl\Models\Admin::first();
        if (!$user) return;

        Notification::insert([
            [
                'type' => 'System',
                'notifiable_type' => get_class($user),
                'notifiable_id' => $user->id,
                'data' => json_encode(['title' => 'Welcome!', 'body' => 'Your account has been created.']),
                'created_at' => Carbon::now()->subDays(2),
                'updated_at' => Carbon::now()->subDays(2),
            ],
            [
                'type' => 'System',
                'notifiable_type' => get_class($user),
                'notifiable_id' => $user->id,
                'data' => json_encode(['title' => 'System Alert', 'body' => 'Scheduled maintenance tonight at 11pm.']),
                'created_at' => Carbon::now()->subDay(),
                'updated_at' => Carbon::now()->subDay(),
            ],
            [
                'type' => 'System',
                'notifiable_type' => get_class($user),
                'notifiable_id' => $user->id,
                'data' => json_encode(['title' => 'Reminder', 'body' => 'Don\'t forget to update your profile.']),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ]);
    }
}
