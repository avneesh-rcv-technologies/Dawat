<?php

namespace Modules\KYC\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Notifications\Models\NotificationChannel;
use Modules\Notifications\Models\NotificationEvent;
use Modules\Notifications\Models\NotificationMapping;
use Modules\EmailNotification\Models\NotificationTemplate as EmailTemplate;
use Modules\EmailNotification\Support\DawatEmail;

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
                'notification_class' => \Modules\KYC\Notifications\KycRequestNotification::class,
                'name' => 'KYC Request',
                'desc' => 'Sent when admin requests KYC documents from a restaurant owner.',
                'email_subject' => 'KYC Document Request - {{kyc_type_name}}',
                'email_body' => $this->email(
                    'DOCUMENTS REQUESTED',
                    'Documents requested<br>for verification',
                    'You have been asked to submit your <strong style="color:#ffffff;">{{kyc_type_name}}</strong> documents for verification.',
                    [
                        DawatEmail::details('Request details', ['Document type' => '{{kyc_type_name}}']),
                        DawatEmail::callout('Message from the review team', '{{request_message}}'),
                        DawatEmail::text('Please submit the required documents at your earliest convenience.'),
                    ]
                ),
                'notify_owner' => true,
            ],
            [
                'code' => 'compliance_missing',
                'notification_class' => \Modules\KYC\Notifications\ComplianceMissingNotification::class,
                'name' => 'Compliance Document Missing',
                'desc' => 'Reminder when a required compliance document has not been uploaded.',
                'email_subject' => 'Action required: {{document_type}}',
                'email_body' => $this->email(
                    'ACTION REQUIRED',
                    'A compliance document<br>is required',
                    'Please upload your <strong style="color:#ffffff;">{{document_type}}</strong> to remain compliant.',
                    [DawatEmail::details('Document', ['Document type' => '{{document_type}}', 'Status' => 'Missing'])]
                ),
                'notify_owner' => true,
            ],
            [
                'code' => 'compliance_status',
                'notification_class' => \Modules\KYC\Notifications\ComplianceStatusNotification::class,
                'name' => 'Compliance Document Active',
                'desc' => 'Sent when a compliance submission is approved and the record is active.',
                'email_subject' => '{{document_type}} is now active',
                'email_body' => $this->email(
                    'COMPLIANCE APPROVED',
                    'Your document<br>is approved',
                    'Your <strong style="color:#ffffff;">{{document_type}}</strong> has been approved and is now active.',
                    [DawatEmail::details('Document', ['Document type' => '{{document_type}}', 'Status' => 'Active', 'Valid until' => '{{expires_at}}'])]
                ),
                'notify_owner' => true,
            ],
            [
                'code' => 'compliance_expiring_soon',
                'notification_class' => \Modules\KYC\Notifications\ComplianceExpiringSoonNotification::class,
                'name' => 'Compliance Expiring Soon',
                'desc' => 'Reminder before a compliance document expires.',
                'email_subject' => '{{document_type}} expiring on {{expires_at}}',
                'email_body' => $this->email(
                    'RENEWAL REMINDER',
                    'Your document<br>expires soon',
                    'Your <strong style="color:#ffffff;">{{document_type}}</strong> expires on {{expires_at}}. Please renew it in time.',
                    [DawatEmail::details('Document', ['Document type' => '{{document_type}}', 'Expires on' => '{{expires_at}}'])]
                ),
                'notify_owner' => true,
            ],
            [
                'code' => 'compliance_expired',
                'notification_class' => \Modules\KYC\Notifications\ComplianceExpiredNotification::class,
                'name' => 'Compliance Expired',
                'desc' => 'Sent when a compliance document has expired.',
                'email_subject' => '{{document_type}} has expired',
                'email_body' => $this->email(
                    'COMPLIANCE EXPIRED',
                    'Your document<br>has expired',
                    'Your <strong style="color:#ffffff;">{{document_type}}</strong> expired on {{expires_at}}. Upload a renewed document to continue.',
                    [DawatEmail::details('Document', ['Document type' => '{{document_type}}', 'Expired on' => '{{expires_at}}'])]
                ),
                'notify_owner' => true,
            ],
        ];

        foreach ($definitions as $def) {
            $event = NotificationEvent::updateOrCreate(
                ['event_code' => $def['code']],
                ['event_name' => $def['name'], 'event_description' => $def['desc'], 'notification_class' => $def['notification_class']]
            );

            $emailTpl = EmailTemplate::updateOrCreate(
                ['name' => $def['name'], 'locale' => 'en'],
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

    private function email(string $badge, string $headline, string $intro, array $blocks): string
    {
        return DawatEmail::render([
            'preheader' => strip_tags($intro),
            'badge' => $badge,
            'headline' => $headline,
            'greeting' => 'Hello {{owner_name}},',
            'intro' => $intro,
            'blocks' => $blocks,
            'footer' => 'You received this email because you manage a restaurant on Dawat.',
        ]);
    }
}
