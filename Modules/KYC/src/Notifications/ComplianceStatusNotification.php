<?php

namespace Modules\KYC\Notifications;

use Modules\KYC\Notifications\Concerns\BuildsComplianceNotificationPayload;
use Modules\Notifications\Contracts\Notification;

class ComplianceStatusNotification implements Notification
{
    use BuildsComplianceNotificationPayload;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    protected function complianceEventCode(): string
    {
        return 'compliance_status';
    }

    protected function defaultTitle(): string
    {
        return 'Compliance document approved';
    }

    protected function defaultMessage(): string
    {
        return 'Your compliance document is active.';
    }
}
