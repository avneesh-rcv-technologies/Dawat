<?php

namespace Modules\Acl\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Acl\Notifications\AdminEmailVerificationNotification;
use Modules\EmailNotification\Models\NotificationTemplate;
use Modules\Notifications\Models\NotificationChannel;
use Modules\Notifications\Models\NotificationEvent;
use Modules\Notifications\Models\NotificationMapping;

class AdminEmailVerificationSeeder extends Seeder
{
    public function run(): void
    {
        $event = NotificationEvent::firstOrCreate(['event_code' => AdminEmailVerificationNotification::EVENT], [
            'event_name' => 'Admin Email Verification',
            'event_description' => 'Verify the account email before signing in to the admin panel.',
            'notification_class' => AdminEmailVerificationNotification::class,
        ]);
        $template = NotificationTemplate::firstOrCreate(['name' => AdminEmailVerificationNotification::EVENT], [
            'subject' => 'Verify your email - {{app_name}}',
            'content' => <<<'HTML'
<div style="font-family:Arial,sans-serif;max-width:600px;margin:auto;padding:32px;">
    <h1>Verify your email</h1>
    <p>Hello {{admin_name}},</p>
    <p>Please verify your email before signing in to {{app_name}}.</p>
    <p><a href="{{verification_url}}" style="display:inline-block;background:#000;color:#fff;padding:14px 24px;border-radius:8px;text-decoration:none;">Verify email</a></p>
    <p>This link expires in {{expires_minutes}} minutes. After verifying, return to the login page and sign in.</p>
    <p>If you did not request this email, you can ignore it.</p>
</div>
HTML,
            'status' => true,
            'locale' => 'en',
        ]);
        $channel = NotificationChannel::where('name', 'email')->first();
        if ($channel) {
            NotificationMapping::firstOrCreate(['event_id' => $event->id, 'channel_id' => $channel->id], [
                'template_id' => (string) $template->id,
                'recipients' => ['notify_admin' => true],
            ]);
        }
    }
}
