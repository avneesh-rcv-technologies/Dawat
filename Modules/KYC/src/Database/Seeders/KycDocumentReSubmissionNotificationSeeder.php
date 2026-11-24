<?php

namespace Modules\KYC\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\EmailNotification\Support\SeederEmailView;
use Modules\EmailNotification\Models\NotificationTemplate;
use Modules\KYC\Notifications\KycDocumentReSubmissionNotification;
use Modules\Notifications\Models\NotificationChannel;
use Modules\Notifications\Models\NotificationEvent;
use Modules\Notifications\Models\NotificationMapping;

class KycDocumentReSubmissionNotificationSeeder extends Seeder
{
    public function run(): void
    {
        $event = NotificationEvent::updateOrCreate(['event_code' => 'kyc_document_re_submission'], [
            'event_name' => 'KYC Document Re-Submission',
            'event_description' => 'Confirms that corrected or replacement KYC documents have been resubmitted for review.',
            'notification_class' => KycDocumentReSubmissionNotification::class,
        ]);
        $template = $this->seedEmailTemplate();
        $channel = NotificationChannel::where('name', 'email')->first();
        if ($channel) {
            NotificationMapping::updateOrCreate(['event_id' => $event->id, 'channel_id' => $channel->id], [
                'template_id' => (string) $template->id,
                'recipients' => ['notify_owner' => true, 'notify_admin' => false],
            ]);
        }
    }

    public function seedEmailTemplate(): NotificationTemplate
    {
        return NotificationTemplate::updateOrCreate(['name' => 'kyc_document_re_submission', 'locale' => 'en'], [
            'subject' => 'KYC documents resubmitted — Dawat',
            'content' => SeederEmailView::make('kyc::emails.notification', [
                'kind' => 'resubmission',
                'heading' => 'Your updated documents are with us',
                'badge' => 'KYC DOCUMENTS RESUBMITTED',
                'ownerName' => '{{owner_name}}',
                'intro' => 'Your corrected or replacement KYC documents have been received and are awaiting a new review by the Dawat team.',
                'detailsHeading' => 'RESUBMISSION DETAILS',
                'rows' => [
                    'Document types' => '{{document_types}}',
                    'Status' => 'Resubmitted — awaiting review', 'Resubmitted on' => '{{resubmitted_at}}',
                ],
                'feedbackHtml' => '',
                'contentLabel' => 'Resubmission update',
                'contentText' => '{{request_message}}',
                'instructions' => 'The review team will check the updated documents. The restaurant owner will receive an update when they are approved or further corrections are needed.',
                'loginUrl' => '{{login_url}}', 'submissionUrl' => '{{submission_url}}',
                'supportUrl' => '{{support_url}}', 'currentYear' => '{{current_year}}',
            ])->render(),
            'status' => true,
        ]);
    }
}
