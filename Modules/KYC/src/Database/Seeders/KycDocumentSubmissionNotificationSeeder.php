<?php

namespace Modules\KYC\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\EmailNotification\Support\SeederEmailView;
use Modules\Notifications\Models\NotificationChannel;
use Modules\Notifications\Models\NotificationEvent;
use Modules\Notifications\Models\NotificationMapping;
use Modules\EmailNotification\Models\NotificationTemplate as EmailTemplate;

class KycDocumentSubmissionNotificationSeeder extends Seeder
{
    public function run(): void
    {
        $emailChannel = NotificationChannel::where('name', 'email')->first();

        if (! $emailChannel) {
            $this->command->warn('KycDocumentSubmissionNotificationSeeder skipped: email channel not found.');
            return;
        }

        $event = NotificationEvent::updateOrCreate(
            ['event_code' => 'kyc_document_submission'],
            [
                'notification_class' => \Modules\KYC\Notifications\KycDocumentSubmissionNotification::class,
                'event_name' => 'KYC Document Submission',
                'event_description' => 'Sent when restaurant_owner KYC documents are submitted for review.',
            ]
        );

        $emailTemplate = $this->seedEmailTemplate();

        NotificationMapping::updateOrCreate(
            ['event_id' => $event->id, 'channel_id' => $emailChannel->id],
            [
                'template_id' => $emailTemplate->id,
                'recipients' => [
                    'notify_owner' => true,
                    'notify_admin' => false,
                ],
            ]
        );

        $this->command->info('KYC document submission notification seeded successfully.');
    }

    public function seedEmailTemplate(): EmailTemplate
    {
        return EmailTemplate::updateOrCreate(['name' => 'KYC Document Submission', 'locale' => 'en'], [
            'subject' => 'KYC documents received — Dawat',
            'content' => SeederEmailView::make('kyc::emails.notification', [
                'kind' => 'submission',
                'heading' => 'Your documents are with us',
                'badge' => 'KYC DOCUMENTS RECEIVED',
                'ownerName' => '{{owner_name}}',
                'intro' => 'Your KYC documents have been received and are awaiting review by the Dawat team.',
                'detailsHeading' => 'SUBMISSION DETAILS',
                'rows' => ['Document types' => '{{document_types}}', 'Documents submitted' => '{{submission_count}}', 'Status' => 'Submitted — awaiting review', 'Submitted on' => '{{submitted_at}}'],
                'feedbackHtml' => '{{status_feedback_html}}',
                'contentLabel' => 'Submission update',
                'contentText' => '{{request_message}}',
                'instructions' => 'The review team will check the documents. The restaurant owner will receive an update when a document is approved or corrections are needed.',
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
