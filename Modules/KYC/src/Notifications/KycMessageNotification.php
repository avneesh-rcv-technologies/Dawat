<?php

namespace Modules\KYC\Notifications;

use Illuminate\Notifications\Notification;
use Modules\Notifications\Contracts\Notification as NotificationContract;
use Modules\Notifications\Support\NotificationMappingLoader;
use Modules\KYC\Models\KycSubmission;
use Modules\KYC\Models\KycMessage;

class KycMessageNotification extends Notification implements NotificationContract
{
    protected $data;
    protected $submission;
    protected $message;

    public function __construct($data)
    {
        $this->data = $data;
        if (isset($data['data']['submission_id'])) {
            $this->submission = KycSubmission::find($data['data']['submission_id']);
        }
        if (isset($data['data']['message_id'])) {
            $this->message = KycMessage::find($data['data']['message_id']);
        }
    }

    /**
     * Get the notification's event code
     */
    public function getEventCode(): string
    {
        return 'kyc_message';
    }

    /**
     * Get variables for template replacement
     */
    public function getVariables(): array
    {
        $recipientName = $this->submission->user->name ?? $this->submission->restaurant_owner->name ?? $this->data['owner_name'] ?? 'Restaurant Owner';
        $messageContent = $this->message->message ?? $this->data['message_content'] ?? '';
        $kycTypeName = $this->submission->kycType->name ?? $this->data['kyc_type_name'] ?? 'KYC Document';
        $submissionUrl = $this->data['submission_url'] ?? url('/restaurant/kyc/history');

        return [
            'owner_name' => $recipientName,
            'message_content' => $messageContent,
            'kyc_type_name' => $kycTypeName,
            'submission_url' => $submissionUrl,
        ];
    }

    public function getAvailableRecipients(): array
    {
        return [
            'notify_owner' => 'Notify Restaurant Owner',
        ];
    }

    public function getRecipientTypeMapping(): array
    {
        return [
            'notify_owner' => 'notifiable',
        ];
    }

    public function getRecipientClassMapping(): array
    {
        return [
            'notify_owner' => \Illuminate\Database\Eloquent\Model::class,
        ];
    }

    public function getAvailableVariables(): array
    {
        return [
            'owner_name' => 'Restaurant Owner Name',
            'message_content' => 'Message Content',
            'kyc_type_name' => 'KYC Type Name',
            'submission_url' => 'Submission URL',
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
            'title' => 'New Message on KYC Submission',
            'message' => 'You have received a new message regarding your KYC submission.',
            'data' => [
                'submission_id' => $this->submission->id,
                'message_id' => $this->message->id,
                'content' => $this->message->message
            ]
        ];
    }

    /**
     * Get the mail representation of the notification (fallback)
     */
    public function toMail($notifiable)
    {
        return (new \Illuminate\Notifications\Messages\MailMessage)
            ->subject('New Message regarding your KYC Submission')
            ->line('You have received a new message from the admin:')
            ->line('"' . $this->message->message . '"')
            ->action('View Message', url('/restaurant/kyc/history'))
            ->line('Please log in to reply.');
    }
}
