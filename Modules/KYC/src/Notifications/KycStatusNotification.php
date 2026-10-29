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
        $variables = [
            'notification_type' => 'kyc_status_update',
            'owner_name' => $this->data['owner_name'] ?? $this->data['user_name'] ?? 'Restaurant Owner',
            'user_name' => $this->data['user_name'] ?? $this->data['owner_name'] ?? 'Restaurant Owner',
            'status_message' => $this->data['status_message'] ?? 'Your KYC submission status has been updated.',
            'kyc_type_name' => $this->data['kyc_type_name'] ?? 'KYC Document',
            'document_type' => $this->data['document_type'] ?? 'KYC Document',
            'status' => $this->data['status'] ?? '',
            'submission_url' => url('/restaurant/kyc/history'),
            'rejection_reason' => $this->data['rejection_reason'] ?? '',
            'reviewed_at' => $this->data['reviewed_at'] ?? '',
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
        ];
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
