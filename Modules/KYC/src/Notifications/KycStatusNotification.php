<?php

namespace Modules\KYC\Notifications;

use Illuminate\Notifications\Notification;
use Modules\Notifications\Support\NotificationMappingLoader;
use Modules\Activitylog\Exceptions\ApiExceptionHandler;
use Modules\Notifications\Contracts\Notification as NotificationContract;
use Modules\KYC\Models\KycSubmission;
use Exception;
use Illuminate\Support\Facades\Log;

class KycStatusNotification extends Notification implements NotificationContract
{
    use \Modules\KYC\Notifications\Concerns\BuildsKycEmailVariables;

    protected $data;
    protected $submission;

    public function __construct($data)
    {
        $this->data = $data;
        if (isset($data['data']['submission_id'])) {
            $this->submission = KycSubmission::find($data['data']['submission_id']);
        }
    }

    /**
     * Get the notification's event code
     */
    public function getEventCode(): string
    {
        return 'kyc_status';
    }

    /**
     * Get variables for template replacement
     */
    public function getVariables(): array
    {
        $status = $this->data['status'] ?? $this->submission?->status ?? '';
        $reason = $this->data['rejection_reason'] ?? $this->submission?->rejection_reason ?? '';
        $needsCorrections = in_array($status, ['rejected', 'changes_requested', 'action_required'], true);
        $document = $this->data['document_type'] ?? $this->data['kyc_type_name'] ?? $this->submission?->kycType?->name ?? 'KYC Document';
        $variables = $this->kycEmailVariables() + [
            'notification_type' => 'kyc_status_update',
            'owner_name' => e($this->data['owner_name'] ?? $this->data['user_name'] ?? $this->submission?->user?->name ?? 'Restaurant Owner'),
            'user_name' => $this->data['user_name'] ?? $this->data['owner_name'] ?? 'Restaurant Owner',
            'status_message' => e($this->data['status_message'] ?? 'Your KYC submission status has been updated.'),
            'kyc_type_name' => e($this->data['kyc_type_name'] ?? $document),
            'document_type' => e($document),
            'status' => $status,
            'status_label' => match ($status) {
                'changes_requested', 'action_required' => 'Corrections requested',
                '' => 'Not recorded',
                default => \Illuminate\Support\Str::headline($status),
            },
            'status_time_label' => in_array($status, ['approved', 'rejected'], true) ? 'Reviewed on' : 'Status updated on',
            'rejection_reason' => e($reason),
            'reviewed_at' => e($this->data['reviewed_at'] ?? $this->submission?->reviewed_at?->format('M d, Y h:i A T') ?? 'Not recorded'),
            'status_feedback_html' => $needsCorrections ? view('kyc::emails.partials.status-feedback', [
                'reason' => $reason, 'refill' => $status !== 'rejected',
            ])->render() : '',
            'status_next_steps' => match ($status) {
                'approved' => 'This document is approved. Check any remaining KYC documents and onboarding steps in your restaurant account.',
                'rejected', 'changes_requested', 'action_required' => 'Log in, open KYC Documents, correct the requested information or upload a replacement document, and resubmit it for review.',
                default => 'The review team is checking your document. Log in to view its latest status and any messages.',
            },
            'company_name' => $this->data['company_name'] ?? config('app.name', 'Our Company'),
            'current_year' => $this->data['current_year'] ?? date('Y'),
        ];

        return $variables;
    }

    public function getAvailableRecipients(): array
    {
        return [
            'notify_owner' => 'Notify Restaurant Owner',
            'notify_admin' => 'Notify Admin',
        ];
    }

    public function getRecipientTypeMapping(): array
    {
        return [
            'notify_owner' => 'notifiable',
            'notify_admin' => 'admin',
        ];
    }

    public function getRecipientClassMapping(): array
    {
        return [
            'notify_owner' => \Illuminate\Database\Eloquent\Model::class,
            'notify_admin' => \Modules\Acl\Models\Admin::class,
        ];
    }

    public function getAvailableVariables(): array
    {
        return [
            'owner_name' => 'Restaurant Owner Name',
            'status_message' => 'Status Message',
            'kyc_type_name' => 'KYC Type Name',
            'document_type' => 'Document Type',
            'status' => 'Status',
            'submission_url' => 'Submission URL',
            'rejection_reason' => 'Rejection Reason',
            'reviewed_at' => 'Review timestamp',
            'status_label' => 'Readable document status', 'status_time_label' => 'Review or status update time label',
            'status_feedback_html' => 'Correction or rejection feedback, when applicable',
            'status_next_steps' => 'Instructions for the current document status',
        ] + $this->kycEmailAvailableVariables();
    }

    /**
     * Get the template identifier
     */


    public function getTemplateIdentifier(): array
    {
        return NotificationMappingLoader::forEventCode($this->getEventCode());
    }

    /**
     * Get the array representation of the notification
     */
    public function toArray($notifiable = null): array
    {
        return [
            'title' => 'KYC Status Update',
            'message' => $this->data['status_message'] ?? 'Your KYC submission status has been updated.',
            'data' => [
                'status' => $this->data['status'] ?? '',
                'rejection_reason' => $this->data['rejection_reason'] ?? ''
            ]
        ];
    }

}
