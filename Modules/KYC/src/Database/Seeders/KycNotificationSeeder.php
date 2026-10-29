<?php

namespace Modules\KYC\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Notifications\Models\NotificationChannel;
use Modules\Notifications\Models\NotificationEvent;
use Modules\Notifications\Models\NotificationMapping;
use Modules\EmailNotification\Models\NotificationTemplate as EmailTemplate;

class KycNotificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $emailChannel = NotificationChannel::where('name', 'email')->first();
        $databaseChannel = NotificationChannel::where('name', 'database')->first();

        if (! $emailChannel) {
            $this->command->warn('KycNotificationSeeder skipped: email channel not found.');
            return;
        }

        $definitions = [
            [
                'code' => 'kyc_request',
                'name' => 'KYC Request',
                'desc' => 'Sent when admin requests KYC documents from a restaurant owner.',
                'email_subject' => 'KYC Document Request - {{kyc_type_name}}',
                'email_body' => $this->wrap(
                    'KYC Document Request',
                    '<p style="margin:0 0 16px;">Hello {{owner_name}},</p>' .
                    '<p style="margin:0 0 20px;">You have been requested to submit your <strong>{{kyc_type_name}}</strong> documents for verification.</p>' .
                    '<p style="margin:0 0 12px;">{{request_message}}</p>' .
                    $this->infoTable([
                        'Document Type' => '{{kyc_type_name}}',
                    ]) .
                    '<p style="margin:0 0 24px;">Please submit the required documents at your earliest convenience.</p>' .
                    '<p style="margin:0;font-size:13px;color:#6b7280;">{{company_signature}}</p>'
                ),
                'notify_owner' => true,
            ],
            [
                'code' => 'compliance_missing',
                'name' => 'Compliance Document Missing',
                'desc' => 'Reminder when a required compliance document has not been uploaded.',
                'email_subject' => 'Action required: {{document_type}}',
                'email_body' => $this->wrap(
                    'Compliance document required',
                    '<p style="margin:0 0 16px;">Hello {{owner_name}},</p>' .
                    '<p style="margin:0;">Please upload your <strong>{{document_type}}</strong> to remain compliant.</p>'
                ),
                'notify_owner' => true,
            ],
            [
                'code' => 'compliance_status',
                'name' => 'Compliance Document Active',
                'desc' => 'Sent when a compliance submission is approved and the record is active.',
                'email_subject' => '{{document_type}} is now active',
                'email_body' => $this->wrap(
                    'Compliance approved',
                    '<p style="margin:0 0 16px;">Hello {{owner_name}},</p>' .
                    '<p style="margin:0;">Your <strong>{{document_type}}</strong> is active until {{expires_at}}.</p>'
                ),
                'notify_owner' => true,
            ],
            [
                'code' => 'compliance_expiring_soon',
                'name' => 'Compliance Expiring Soon',
                'desc' => 'Reminder before a compliance document expires.',
                'email_subject' => '{{document_type}} expiring on {{expires_at}}',
                'email_body' => $this->wrap(
                    'Renewal reminder',
                    '<p style="margin:0 0 16px;">Hello {{owner_name}},</p>' .
                    '<p style="margin:0;">Your <strong>{{document_type}}</strong> expires on {{expires_at}}. Please renew it in time.</p>'
                ),
                'notify_owner' => true,
            ],
            [
                'code' => 'compliance_expired',
                'name' => 'Compliance Expired',
                'desc' => 'Sent when a compliance document has expired.',
                'email_subject' => '{{document_type}} has expired',
                'email_body' => $this->wrap(
                    'Compliance expired',
                    '<p style="margin:0 0 16px;">Hello {{owner_name}},</p>' .
                    '<p style="margin:0;">Your <strong>{{document_type}}</strong> expired on {{expires_at}}. Upload a renewed document to continue.</p>'
                ),
                'notify_owner' => true,
            ],
        ];

        foreach ($definitions as $def) {
            $event = NotificationEvent::updateOrCreate(
                ['event_code' => $def['code']],
                ['event_name' => $def['name'], 'event_description' => $def['desc']]
            );

            $emailTpl = EmailTemplate::updateOrCreate(
                ['name' => $def['name']],
                ['subject' => $def['email_subject'], 'content' => $def['email_body'], 'status' => true]
            );

            NotificationMapping::updateOrCreate(
                ['event_id' => $event->id, 'channel_id' => $emailChannel->id],
                [
                    'template_id' => $emailTpl->id,
                    'recipients' => [
                        'notify_owner' => $def['notify_owner'],
                    ],
                ]
            );

            if ($databaseChannel) {
                NotificationMapping::updateOrCreate(
                    ['event_id' => $event->id, 'channel_id' => $databaseChannel->id],
                    [
                        'recipients' => [
                            'notify_owner' => $def['notify_owner'],
                        ],
                    ]
                );
            }

            $this->command->info("KYC notification event '{$def['code']}' seeded.");
        }
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
                        '<td style="padding:12px 16px;background-color:#f9fafb;border:1px solid #e5e7eb;font-weight:600;color:#374151;width:40%;">' . $label . '</td>' .
                        '<td style="padding:12px 16px;border:1px solid #e5e7eb;color:#111827;">' . $value . '</td>' .
                    '</tr>';
            }

            return '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;margin:0 0 4px;font-size:14px;">' . $body . '</table>';
        }

        /**
         * Render a prominent OTP display block.
         */
        private function otpBlock(string $otp): string
        {
            return
                '<div style="text-align:center;margin:24px 0;">' .
                    '<span style="display:inline-block;padding:14px 32px;background-color:#111827;color:#ffffff;font-size:28px;font-weight:700;letter-spacing:6px;border-radius:6px;">' . $otp . '</span>' .
                '</div>';
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
