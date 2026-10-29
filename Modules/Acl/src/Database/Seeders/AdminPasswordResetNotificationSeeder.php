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
        $template = NotificationTemplate::firstOrCreate(['name' => AdminPasswordResetNotification::EVENT], [
            'subject' => 'Reset your password - {{app_name}}',
            'content' => <<<'HTML'
<div style="font-family:Arial,sans-serif;max-width:600px;margin:auto;background:#fff;color:#103647;padding:32px;">
    <h1 style="margin:0 0 24px;">Reset your password</h1>
    <p>Hello {{admin_name}},</p>
    <p>We received a request to reset your {{app_name}} account password.</p>
    <p><a href="{{reset_url}}" style="display:inline-block;background:#103647;color:#fff;padding:14px 24px;border-radius:8px;text-decoration:none;">Reset Password</a></p>
    <p>This link expires in {{expire_minutes}} minutes and can be used only once.</p>
    <p>If you did not request a password reset, you can ignore this email.</p>
</div>
HTML,
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
