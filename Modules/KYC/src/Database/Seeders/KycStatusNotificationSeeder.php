<?php

namespace Modules\KYC\Database\Seeders;

use Illuminate\Database\Seeder;
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
                'event_name' => 'KYC Status Update',
                'event_description' => 'Notification sent when KYC or onboarding status changes (approved/rejected/under review)',
            ]
        );
        echo "Event created/updated (ID: {$event->id})\n\n";

        // 2. Create Email Template
        echo " Creating email template...\n";
        $emailTemplate = EmailTemplate::updateOrCreate(
            ['name' => 'KYC Status Update'],
            [
                'subject' => 'Your KYC Status Update',
                'content' => $this->getEmailTemplate(),
                'status' => true
            ]
        );
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

    protected function getEmailTemplate(): string
    {
        $details = $this->infoTable([
            'Document Type' => '{{document_type}}',
            'Status' => '<strong style="color:#0f766e;">{{status}}</strong>',
            'Reviewed At' => '{{reviewed_at}}',
        ]);

        $inner = '<p style="margin:0 0 16px;">Hello {{owner_name}},</p>' .
                 '<p style="margin:0 0 16px;">{{status_message}}</p>' .
                 $details .
                 '<p style="margin:18px 0 16px; color:#1f2937; font-size:14px; line-height:1.6;"><strong>Rejection Reason:</strong> {{rejection_reason}}</p>' .
                 '<p style="margin:18px 0 0; color:#1f2937; font-size:14px; line-height:1.6;">Thank you for completing your onboarding with {{company_name}}.</p>';

        return $this->wrap('KYC Status Update', $inner);
    }

    /**
     * Keep the template focused on message content. The shared branded
     * layout is applied by NotificationTemplate.
     */
    private function wrap(string $heading, string $innerHtml): string
    {
        return
            '<h1 style="margin:0 0 20px;font-size:20px;font-weight:700;color:#111827;">' . $heading . '</h1>' .
            '<div style="font-size:15px;line-height:1.6;color:#374151;">' . $innerHtml . '</div>';
    }

    /**
     * Render a key/value details table used across several templates.
     */
    private function infoTable(array $rows): string
    {
        $body = '';
        foreach ($rows as $label => $value) {
            $body .=
                '<tr>' .
                    '<td style="padding:12px 16px;background:#f9fafb;border:1px solid #e5e7eb;font-weight:600;color:#374151;width:40%;">' . $label . '</td>' .
                    '<td style="padding:12px 16px;border:1px solid #e5e7eb;color:#111827;">' . $value . '</td>' .
                '</tr>';
        }

        return '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;margin:0 0 4px;font-size:14px;">' . $body . '</table>';
    }

    /**
     * Render a small colored status pill.
     */
    private function statusBadge(string $label, string $color, string $bg): string
    {
        return
            '<span style="display:inline-block;padding:6px 14px;border-radius:999px;background-color:' . $bg . ';color:' . $color . ';font-size:13px;font-weight:600;">' . $label . '</span>';
    }
    }
