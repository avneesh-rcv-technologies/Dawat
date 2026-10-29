<?php

namespace Modules\KYC\Notifications;

use Modules\KYC\Notifications\Concerns\BuildsComplianceNotificationPayload;
use Modules\Notifications\Contracts\Notification;

class ComplianceMissingNotification implements Notification
{
    use BuildsComplianceNotificationPayload;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    protected function complianceEventCode(): string
    {
        return 'compliance_missing';
    }

    protected function defaultTitle(): string
    {
        return 'Compliance document required';
    }

    protected function defaultMessage(): string
    {
        $type = $this->data['document_type'] ?? 'compliance document';

        return "Please upload your {$type} to stay compliant.";
    }
}
