<?php

namespace Modules\KYC\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Notifications\Models\NotificationChannel;
use Modules\Notifications\Models\NotificationEvent;
use Modules\Notifications\Models\NotificationMapping;
use Modules\EmailNotification\Models\NotificationTemplate as EmailTemplate;

class KycMessageNotificationSeeder extends Seeder
{
    public function run(): void
    {
        $emailChannel = NotificationChannel::where('name', 'email')->first();
        if (! $emailChannel) {
            $this->command->warn('KycMessageNotificationSeeder skipped: email channel not found.');
            return;
        }

        $event = NotificationEvent::updateOrCreate(
            ['event_code' => 'kyc_message'],
            [
                'event_name' => 'KYC Message',
                'event_description' => 'Notification sent when a new message is posted on a restaurant owner KYC submission.',
            ]
        );

        $emailTemplate = EmailTemplate::updateOrCreate(
            ['name' => 'KYC Message Notification'],
            [
                'subject' => 'New KYC Message - {{kyc_type_name}}',
                'content' => $this->getEmailTemplate(),
                'status' => true,
            ]
        );

        NotificationMapping::updateOrCreate(
            ['event_id' => $event->id, 'channel_id' => $emailChannel->id],
            [
                'template_id' => $emailTemplate->id,
                'recipients' => [
                    'notify_owner' => true,
                ],
            ]
        );

        $webPushModel = 'Modules\\WebPushNotification\\Models\\NotificationTemplate';
        if (class_exists($webPushModel)) {
            $webPushChannel = NotificationChannel::where('name', 'webpush')->first();
            if ($webPushChannel) {
                $webPushTemplate = $webPushModel::updateOrCreate(
                    ['name' => 'KYC Message Notification'],
                    [
                        'subject' => 'New message on your KYC submission',
                        'body' => 'You have a new message for {{kyc_type_name}}.',
                        'is_active' => true,
                    ]
                );

                NotificationMapping::updateOrCreate(
                    ['event_id' => $event->id, 'channel_id' => $webPushChannel->id],
                    [
                        'template_id' => $webPushTemplate->id,
                        'recipients' => [
                            'notify_owner' => true,
                        ],
                    ]
                );
            }
        }

        $this->command->info('KYC message notification seeded successfully.');
    }

    protected function getEmailTemplate(): string
    {
        return <<<HTML
<h1 style="margin:0 0 20px;font-size:20px;font-weight:700;color:#111827;">New KYC Message</h1>
<div style="font-size:15px;line-height:1.6;color:#374151;">
    <p style="margin:0 0 16px;">Hello {{owner_name}},</p>
    <p style="margin:0 0 20px;">You have received a new message regarding your <strong>{{kyc_type_name}}</strong> KYC submission.</p>
    <div style="margin:0 0 24px;padding:18px 20px;background:#f9fafb;border:1px solid #e5e7eb;border-radius:8px;color:#111827;">
        {{message_content}}
    </div>
</div>
HTML;
    }
}
