<?php

namespace Modules\KYC\Notifications;

use Modules\KYC\Notifications\Concerns\BuildsComplianceNotificationPayload;
use Modules\Notifications\Contracts\Notification;

class ComplianceExpiringSoonNotification implements Notification
{
    use BuildsComplianceNotificationPayload;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    protected function complianceEventCode(): string
    {
        return 'compliance_expiring_soon';
    }

    protected function defaultTitle(): string
    {
        return 'Compliance document expiring soon';
    }

    protected function defaultMessage(): string
    {
        return 'Please renew your compliance document before it expires.';
    }
}
