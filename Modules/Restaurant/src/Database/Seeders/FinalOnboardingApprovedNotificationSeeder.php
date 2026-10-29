<?php

namespace Modules\Restaurant\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\EmailNotification\Models\NotificationTemplate as EmailTemplate;
use Modules\Notifications\Models\NotificationChannel;
use Modules\Notifications\Models\NotificationEvent;
use Modules\Notifications\Models\NotificationMapping;
use Modules\Restaurant\Notifications\FinalOnboardingApprovedNotification;
use Modules\WebPushNotification\Models\NotificationTemplate as WebPushTemplate;

class FinalOnboardingApprovedNotificationSeeder extends Seeder
{
    public function run(): void
    {
        $code = 'final_onboarding_approved';
        $event = NotificationEvent::updateOrCreate(['event_code' => $code], [
            'event_name' => 'Final Onboarding Approved',
            'event_description' => 'Notifies the restaurant owner when all required onboarding steps are approved, with an attached PDF summary.',
        ]);
        $email = EmailTemplate::updateOrCreate(['name' => $code], [
            'subject' => 'Final Onboarding Approved - {{restaurant_name}} - {{app_name}}',
            'content' => <<<'HTML'
<!DOCTYPE html>
<html lang="en"><head><meta charset="UTF-8"><title>Final Onboarding Approved</title></head>
<body style="margin:0;padding:24px;background:#f4f7fa;font-family:Arial,sans-serif;">
<table role="presentation" style="max-width:600px;width:100%;margin:auto;border-collapse:collapse;background:#fff;">
<tr><td style="padding:28px;background:#047857;color:#fff;"><h1 style="margin:0;font-size:24px;">Final Onboarding Approved</h1></td></tr>
<tr><td style="padding:28px;color:#334155;font-size:15px;line-height:1.7;">
<p>All required onboarding steps for <strong>{{restaurant_name}}</strong> have been approved.</p>
<p>Your restaurant is now active. Optional steps do not prevent completion.</p>
<p><strong>Approved at:</strong> {{approved_at}}</p>
<p>Your onboarding review PDF is attached. It includes the saved details, review decisions and timestamps, with bank account and tax identifiers masked.</p>
<p>Thank you,<br>{{app_name}} Team</p>
</td></tr></table></body></html>
HTML,
            'status' => true, 'locale' => 'en',
        ]);
        $notification = new FinalOnboardingApprovedNotification();
        $push = WebPushTemplate::updateOrCreate(['name' => $code], [
            'subject' => 'Final Onboarding Approved',
            'body' => 'All required onboarding steps for {{restaurant_name}} were approved on {{approved_at}}. Your restaurant is now active. Your review PDF is attached to the email.',
            'variables' => collect($notification->getAvailableVariables())
                ->map(fn ($description, $name) => compact('name', 'description'))->values()->all(),
            'is_active' => true,
        ]);
        foreach (['email' => $email, 'webpush' => $push] as $name => $template) {
            $channel = NotificationChannel::where('name', $name)->first();
            if ($channel) {
                NotificationMapping::updateOrCreate(['event_id' => $event->id, 'channel_id' => $channel->id], [
                    'template_id' => (string) $template->id,
                    'recipients' => ['notify_restaurant' => true, 'notify_admin' => false],
                ]);
            }
        }
    }
}
