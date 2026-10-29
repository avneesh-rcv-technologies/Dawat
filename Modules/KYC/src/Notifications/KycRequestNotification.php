<?php

namespace Modules\KYC\Notifications;

use Illuminate\Notifications\Notification;
use Modules\Notifications\Contracts\Notification as NotificationContract;
use Modules\Notifications\Support\NotificationMappingLoader;

class KycRequestNotification extends Notification implements NotificationContract
{
    protected $data;
    protected $request;
    protected $pendingKycTypes = [];
    
    public function __construct($data)
    {
        $this->data = $data;

        if (!empty($data['pending_kyc_types'])) {
            $this->pendingKycTypes = array_values(array_filter(array_map(static function ($type) {
                return is_string($type) ? trim($type) : null;
            }, (array) $data['pending_kyc_types'])));
        }

        // Load the KYC request for additional data
        if (isset($data['data']['request_id'])) {
            $this->request = \Modules\KYC\Models\KycRequest::with('kycType')->find($data['data']['request_id']);
        }
    }
    
    /**
     * Get the notification's event code
     */
    public function getEventCode(): string
    {
        return 'kyc_request';
    }
    
    /**
     * Get variables for template replacement
     */
    public function getVariables(): array
    {
        $requestMessage = $this->data['request_message'] ?? ($this->request->message ?? null);

        // Determine due date: prefer the loaded request model, fall back to supplied data.
        $dueDate = null;
        $rawDue = null;
        if ($this->request && isset($this->request->due_date)) {
            $rawDue = $this->request->due_date;
        } elseif (!empty($this->data['due_date'])) {
            $rawDue = $this->data['due_date'];
        }

        if ($rawDue) {
            try {
                if ($rawDue instanceof \DateTimeInterface) {
                    $dueDate = $rawDue->format('F j, Y');
                } else {
                    $parsed = \Illuminate\Support\Carbon::parse($rawDue);
                    $dueDate = $parsed->format('F j, Y');
                }
            } catch (\Throwable $e) {
                // If parsing fails, leave dueDate as null to avoid breaking notifications
                $dueDate = null;
            }
        }

        $recipientName = $this->data['owner_name'] ?? $this->data['user_name'] ?? 'Restaurant Owner';
        $submissionUrl = !empty($this->data['data']['request_id'])
            ? url('/restaurant/kyc/submit/' . $this->data['data']['request_id'])
            : url('/restaurant/kyc/requests');

        $variables = [
            'title' => $this->data['title'] ?? 'KYC Document Request',
            'owner_name' => $recipientName,
            'kyc_type_name' => !empty($this->pendingKycTypes)
                ? implode(', ', $this->pendingKycTypes)
                : ($this->request ? $this->request->kycType->name : 'KYC Documents'),
            'pending_kyc_types' => !empty($this->pendingKycTypes)
                ? implode(', ', $this->pendingKycTypes)
                : ($this->request ? $this->request->kycType->name : 'KYC Documents'),
            'pending_requests_count' => !empty($this->pendingKycTypes) ? count($this->pendingKycTypes) : 0,
            'submission_url' => $submissionUrl,
        ];

        $variables['request_message'] = $requestMessage ?: 'KYC documents are required for verification. Please submit the necessary documents at your earliest convenience.';

        if ($dueDate) {
            $variables['due_date'] = $dueDate;
        }

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
            'title' => 'Title',
            'owner_name' => 'Restaurant Owner Name',
            'kyc_type_name' => 'KYC Type Name',
            'pending_kyc_types' => 'Pending KYC Types',
            'pending_requests_count' => 'Pending Request Count',
            'submission_url' => 'Submission URL',
            'request_message' => 'Request Message',
            'due_date' => 'Due Date',
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
            'title' => $this->data['title'],
            'message' => $this->data['message'],
            'data' => $this->data['data']
        ];
    }
    
    /**
     * Get the mail representation of the notification (fallback)
     */
    public function toMail($notifiable)
    {
        return (new \Illuminate\Notifications\Messages\MailMessage)
            ->subject($this->data['title'])
            ->line($this->data['message'])
            ->line('Please complete the required documents at your earliest convenience.')
            ->action('View Pending Requests', url('/restaurant/kyc/requests'))
            ->line('Thank you for your cooperation!');
    }
}
