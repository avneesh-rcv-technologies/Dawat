<?php

namespace Modules\KYC\Database\Seeders;

use Illuminate\Database\Seeder;
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
                'event_name' => 'KYC Document Submission',
                'event_description' => 'Sent when restaurant_owner KYC documents are submitted for review.',
            ]
        );

        $emailTemplate = EmailTemplate::updateOrCreate(
            ['name' => 'KYC Document Submission'],
            [
                'subject' => 'KYC Documents Submitted - {{submission_count}} document(s)',
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
                    'notify_admin' => false,
                ],
            ]
        );

        $this->command->info('KYC document submission notification seeded successfully.');
    }

    protected function getEmailTemplate(): string
    {
        return $this->wrap(
            'KYC documents submitted',
            '<p style="margin:0 0 4px;">Hello {{owner_name}},</p>' .
            '<p style="margin:0 0 24px;">Your KYC documents have been submitted successfully for review.</p>' .
            $this->noteBox('{{request_message}}') .
            $this->infoTable([
                'Document types' => '{{document_types}}',
                'Submission count' => '{{submission_count}}',
            ]) .
            $this->footNote('You can review your submission history to track the status of these documents.')
        );
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
     * Render a key/value details table.
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

        return '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;margin:0 0 20px;font-size:14px;border-radius:6px;overflow:hidden;">' . $body . '</table>';
    }

    /**
     * Soft highlighted box for a dynamic message, e.g. admin request notes.
     */
    private function noteBox(string $content): string
    {
        return
            '<div style="margin:0 0 20px;padding:18px 20px;background:#f9fafb;border:1px solid #e5e7eb;border-left:3px solid #111827;border-radius:8px;color:#111827;">' .
                $content .
            '</div>';
    }

    /**
     * Muted footnote paragraph, separated by a hairline rule.
     */
    private function footNote(string $text): string
    {
        return
            '<div style="border-top:1px solid #e5e7eb;margin-top:4px;padding-top:16px;">' .
                '<p style="margin:0;font-size:13px;color:#6b7280;">' . $text . '</p>' .
            '</div>';
    }
}
