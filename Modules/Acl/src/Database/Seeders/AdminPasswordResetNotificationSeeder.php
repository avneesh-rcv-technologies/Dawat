<?php

namespace Modules\Acl\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Acl\Notifications\AdminPasswordResetNotification;
use Modules\EmailNotification\Channels\EmailChannel;
use Modules\EmailNotification\Models\NotificationTemplate;
use Modules\Notifications\Models\NotificationChannel;
use Modules\Notifications\Models\NotificationEvent;
use Modules\Notifications\Models\NotificationMapping;

class AdminPasswordResetNotificationSeeder extends Seeder
{
    public function run(): void
    {
        $event = NotificationEvent::firstOrCreate(['event_code' => AdminPasswordResetNotification::EVENT], [
            'event_name' => 'Admin Password Reset Link',
            'event_description' => 'Send an expiring password reset link to the requesting account owner.',
            'notification_class' => AdminPasswordResetNotification::class,
        ]);
        $template = NotificationTemplate::updateOrCreate(['name' => AdminPasswordResetNotification::EVENT, 'locale' => 'en'], [
            'subject' => 'Reset your password — Dawat',
            'content' => view('acl::emails.admin-password-reset', [
                'accountName' => '{{admin_name}}',
                'resetUrl' => '{{reset_url}}',
                'expireMinutes' => '{{expire_minutes}}',
                'supportUrl' => '{{support_url}}',
                'currentYear' => '{{current_year}}',
            ])->render(),
            'status' => true,
            'locale' => 'en',
        ]);
        $channel = NotificationChannel::firstOrCreate(['name' => 'email'], [
            'channel_class' => EmailChannel::class, 'status' => true, 'config' => [],
        ]);
        NotificationMapping::firstOrCreate(['event_id' => $event->id, 'channel_id' => $channel->id], [
            'template_id' => (string) $template->id,
            'recipients' => ['notify_account_owner' => true],
        ]);
    }
}
