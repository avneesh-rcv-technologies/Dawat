<?php

namespace Modules\Restaurant\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\EmailNotification\Models\NotificationTemplate as EmailTemplate;
use Modules\Notifications\Models\NotificationChannel;
use Modules\Notifications\Models\NotificationEvent;
use Modules\Notifications\Models\NotificationMapping;
use Modules\Restaurant\Notifications\OnboardingRefillRequestedNotification;
use Modules\WebPushNotification\Models\NotificationTemplate as WebPushTemplate;

class OnboardingRefillRequestedNotificationSeeder extends Seeder
{
    public function run(): void
    {
        $notification = new OnboardingRefillRequestedNotification();
        $code = $notification->getEventCode();
        $event = NotificationEvent::updateOrCreate(['event_code' => $code], [
            'event_name' => 'Onboarding Step Alert - Refill Requested',
            'event_description' => 'Notifies the owner when an administrator requests corrected onboarding details or a renewed KYC document, including after approval.',
            'notification_class' => OnboardingRefillRequestedNotification::class,
        ]);
        $email = EmailTemplate::updateOrCreate(['name' => $code], [
            'subject' => 'Onboarding Step Alert - Refill Requested: {{step_name}} - {{app_name}}',
            'content' => $this->template(), 'status' => true, 'locale' => 'en',
        ]);
        $push = WebPushTemplate::updateOrCreate(['name' => $code], [
            'subject' => 'Onboarding Step Alert - Refill Requested',
            'body' => '{{step_name}}: refill requested for {{document_name}}. Reason: {{reason}}. Update and resubmit for review.',
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

    private function template(): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0"><title>Onboarding Step Alert - Refill Requested</title></head>
<body style="margin:0;padding:0;background:#f4f7fa;font-family:Arial,sans-serif;">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0"><tr><td style="padding:40px 20px;">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:600px;margin:auto;background:#fff;border-radius:12px;">
<tr><td style="padding:30px;background:#92400e;color:#fff;border-radius:12px 12px 0 0;"><p style="margin:0 0 8px;font-size:12px;letter-spacing:1px;">ONBOARDING STEP ALERT</p><h1 style="margin:0;font-size:26px;">Refill requested</h1></td></tr>
<tr><td style="padding:30px;color:#334155;font-size:15px;line-height:1.7;">
<p>Hello {{owner_name}},</p>
<p>Our review team has requested an update to the onboarding information for <strong>{{restaurant_name}}</strong>.</p>
<table role="presentation" width="100%" cellspacing="0" cellpadding="8" style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;font-size:14px;">
<tr><td style="color:#64748b;">Onboarding step</td><td><strong>{{step_name}}</strong></td></tr>
<tr><td style="color:#64748b;">Document / details</td><td>{{document_name}}</td></tr>
<tr><td style="color:#64748b;">Requested at</td><td>{{requested_at}}</td></tr>
</table>
<div style="margin:22px 0;padding:18px;background:#fffbeb;border-left:4px solid #d97706;border-radius:6px;"><strong style="color:#92400e;">Reason and required corrections</strong><p style="margin:8px 0 0;white-space:pre-wrap;overflow-wrap:anywhere;">{{reason}}</p></div>
<p>Please refill the requested details or upload the corrected document, then submit the step again. It will require a new review before approval.</p>
<p style="text-align:center;margin:28px 0;"><a href="{{correction_url}}" style="display:inline-block;padding:13px 26px;background:#92400e;color:#fff;border-radius:8px;text-decoration:none;font-weight:600;">Update onboarding step</a></p>
<p style="font-size:13px;color:#64748b;">Sign in with your restaurant account to open the requested step. Previously approved details only reopen when changes are requested.</p>
</td></tr>
<tr><td style="padding:24px;background:#f8fafc;text-align:center;border-radius:0 0 12px 12px;color:#64748b;font-size:13px;">{{app_name}} Team<br>&copy; {{current_year}} {{app_name}}. All rights reserved.</td></tr>
</table></td></tr></table></body></html>
HTML;
    }
}
