<?php

namespace Modules\KYC\Notifications;

use Modules\KYC\Notifications\Concerns\BuildsComplianceNotificationPayload;
use Modules\Notifications\Contracts\Notification;

class ComplianceExpiredNotification implements Notification
{
    use BuildsComplianceNotificationPayload;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    protected function complianceEventCode(): string
    {
        return 'compliance_expired';
    }

    protected function defaultTitle(): string
    {
        return 'Compliance document expired';
    }

    protected function defaultMessage(): string
    {
        return 'Your compliance document has expired. Upload a renewed document to continue.';
    }
}
