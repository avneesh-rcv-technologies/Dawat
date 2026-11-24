<?php

namespace Modules\KYC\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\EmailNotification\Support\SeederEmailView;
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
                'notification_class' => \Modules\KYC\Notifications\KycMessageNotification::class,
                'event_name' => 'KYC Message',
                'event_description' => 'Notification sent when a new message is posted on a restaurant owner KYC submission.',
            ]
        );

        $emailTemplate = $this->seedEmailTemplate();

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

    public function seedEmailTemplate(): EmailTemplate
    {
        return EmailTemplate::updateOrCreate(['name' => 'KYC Message Notification', 'locale' => 'en'], [
            'subject' => 'New KYC message — Dawat',
            'content' => SeederEmailView::make('kyc::emails.notification', [
                'kind' => 'message',
                'heading' => 'A new message for you',
                'badge' => 'NEW KYC MESSAGE',
                'ownerName' => '{{owner_name}}',
                'intro' => 'A new message has been posted about your KYC document. Read it below and log in to reply.',
                'detailsHeading' => 'MESSAGE DETAILS',
                'rows' => ['Document' => '{{kyc_type_name}}', 'Sent on' => '{{sent_at}}'],
                'feedbackHtml' => '{{status_feedback_html}}',
                'contentLabel' => 'Message',
                'contentText' => '{{message_content}}',
                'instructions' => 'Log in to your restaurant account to read the conversation and reply to the review team.',
                'loginUrl' => '{{login_url}}',
                'submissionUrl' => '{{submission_url}}',
                'supportUrl' => '{{support_url}}',
                'currentYear' => '{{current_year}}',
            ])->render(),
            'status' => true,
            'locale' => 'en',
        ]);
    }
}
