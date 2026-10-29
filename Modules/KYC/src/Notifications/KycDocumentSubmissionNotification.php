<?php

namespace Modules\KYC\Notifications;

use Illuminate\Notifications\Notification;
use Modules\Notifications\Contracts\Notification as NotificationContract;
use Modules\Notifications\Support\NotificationMappingLoader;

class KycDocumentSubmissionNotification extends Notification implements NotificationContract
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function getEventCode(): string
    {
        return 'kyc_document_submission';
    }

    public function getTemplateIdentifier(): array
    {
        return NotificationMappingLoader::forEventCode($this->getEventCode());
    }

    public function getVariables(): array
    {
        $recipientName = $this->data['owner_name'] ?? $this->data['owner_name'] ?? 'Restaurant Owner';
        $documentTypes = $this->data['document_types'] ?? [];
        $documentTypeList = is_array($documentTypes)
            ? implode(', ', $documentTypes)
            : trim((string) $documentTypes);

        $dataCount = is_array($documentTypes)
            ? count($documentTypes)
            : ($documentTypeList !== '' ? 1 : 0);

        return [
            'title' => $this->data['title'] ?? 'KYC Documents Submitted',
            'owner_name' => $recipientName,
            'document_types' => $documentTypeList ?: 'KYC documents',
            'submission_count' => $dataCount,
            'submission_url' => $this->data['submission_url'] ?? url('/restaurant/kyc/history'),
            'request_message' => $this->data['request_message'] ?? 'Your documents have been submitted successfully. We will review them shortly.',
        ];
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
            'title' => 'Email Title',
            'owner_name' => 'Restaurant Owner Name',
            'document_types' => 'Submitted Document Types',
            'submission_count' => 'Number of Submitted Documents',
            'submission_url' => 'Submission History URL',
            'request_message' => 'Message to Restaurant Owner',
        ];
    }

    public function toArray($notifiable = null): array
    {
        return [
            'title' => $this->data['title'] ?? 'KYC Documents Submitted',
            'message' => $this->data['request_message'] ?? 'Your documents have been submitted successfully.',
            'data' => [
                'document_types' => $this->data['document_types'] ?? [],
                'submission_count' => is_array($this->data['document_types'] ?? []) ? count($this->data['document_types']) : 1,
            ],
        ];
    }

    public function toMail($notifiable)
    {
        return (new \Illuminate\Notifications\Messages\MailMessage)
            ->subject($this->data['title'] ?? 'KYC Documents Submitted')
            ->line($this->data['request_message'] ?? 'Your documents have been submitted successfully.')
            ->line('Submitted document types: ' . ($this->data['document_types'] ? implode(', ', (array) $this->data['document_types']) : 'KYC documents'))
            ->action('View KYC History', $this->data['submission_url'] ?? url('/restaurant/kyc/history'))
            ->line('Our team will review your submission shortly.');
    }
}
