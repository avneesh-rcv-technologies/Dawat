<?php

namespace Modules\KYC\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\EmailNotification\Support\SeederEmailView;
use Modules\Notifications\Models\NotificationEvent;
use Modules\Notifications\Models\NotificationChannel;
use Modules\Notifications\Models\NotificationMapping;
use Modules\EmailNotification\Models\NotificationTemplate as EmailTemplate;

class KycStatusNotificationSeeder extends Seeder
{
    public function run(): void
    {
        echo "🔧 Setting up KYC Status Notification...\n\n";

        // 1. Create Event
        echo "Creating kyc_status event...\n";
        $event = NotificationEvent::updateOrCreate(
            ['event_code' => 'kyc_status'],
            [
                'notification_class' => \Modules\KYC\Notifications\KycStatusNotification::class,
                'event_name' => 'KYC Status Update',
                'event_description' => 'Notification sent when KYC or onboarding status changes (approved/rejected/under review)',
            ]
        );
        echo "Event created/updated (ID: {$event->id})\n\n";

        // 2. Create Email Template
        echo " Creating email template...\n";
        $emailTemplate = $this->seedEmailTemplate();
        echo " Email template created (ID: {$emailTemplate->id})\n\n";

        // 3. Create WebPush Template (if supported)
        $webPushTemplate = null;
        $webPushModel = 'Modules\\WebPushNotification\\Models\\NotificationTemplate';

        if (class_exists($webPushModel)) {
            echo "📱 Creating webpush template...\n";
            $webPushTemplate = $webPushModel::updateOrCreate(
                ['name' => 'KYC Status Update'],
                [
                    'subject' => 'KYC Status Update',
                    'body' => '{{status_message}}',
                    'is_active' => true
                ]
            );
            echo "WebPush template created (ID: {$webPushTemplate->id})\n\n";
        }

        // 4. Get Channels
        echo "Creating notification mappings...\n";
        $emailChannel = NotificationChannel::where('name', 'email')->first();
        $webPushChannel = NotificationChannel::where('name', 'webpush')->first();

        // 5. Create Email Mapping
        if ($emailChannel && $emailTemplate) {
            NotificationMapping::updateOrCreate(
                [
                    'event_id' => $event->id,
                    'channel_id' => $emailChannel->id,
                ],
                [
                    'template_id' => $emailTemplate->id,
                    'recipients' => [
                        'notify_owner' => true,
                        'notify_admin' => false,
                    ],
                ]
            );
            echo "Email channel mapped\n";
        }

        // 6. Create WebPush Mapping
        if ($webPushChannel && $webPushTemplate) {
            NotificationMapping::updateOrCreate(
                [
                    'event_id' => $event->id,
                    'channel_id' => $webPushChannel->id,
                ],
                [
                    'template_id' => $webPushTemplate->id,
                    'recipients' => [
                        'notify_owner' => true,
                        'notify_admin' => false,
                    ],
                ]
            );
            echo "WebPush channel mapped\n";
        }

        echo "\n✨ KYC Status Notification setup complete!\n";
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        echo "Event Code: kyc_status\n";
        echo "Event ID: {$event->id}\n";
        echo " Email Template ID: {$emailTemplate->id}\n";
        echo " WebPush Template ID: " . ($webPushTemplate ? $webPushTemplate->id : 'N/A') . "\n";
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

        $this->command->info(' KYC Status notification seeded successfully!');
    }

    public function seedEmailTemplate(): EmailTemplate
    {
        return EmailTemplate::updateOrCreate(['name' => 'KYC Status Update', 'locale' => 'en'], [
            'subject' => 'KYC status update — Dawat',
            'content' => SeederEmailView::make('kyc::emails.notification', [
                'kind' => 'status',
                'heading' => 'Your KYC status update',
                'badge' => 'KYC STATUS UPDATE',
                'ownerName' => '{{owner_name}}',
                'intro' => '{{status_message}}',
                'detailsHeading' => 'DOCUMENT STATUS',
                'rows' => ['Document' => '{{document_type}}', 'Status' => '{{status_label}}', '{{status_time_label}}' => '{{reviewed_at}}'],
                'feedbackHtml' => '{{status_feedback_html}}',
                'contentLabel' => '',
                'contentText' => '',
                'instructions' => '{{status_next_steps}}',
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
