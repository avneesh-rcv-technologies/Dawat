<?php

namespace Modules\KYC\Notifications\Concerns;

use Modules\Notifications\Support\NotificationMappingLoader;

trait BuildsComplianceNotificationPayload
{
    protected array $data;

    abstract protected function complianceEventCode(): string;

    abstract protected function defaultTitle(): string;

    abstract protected function defaultMessage(): string;

    public function getEventCode(): string
    {
        return $this->complianceEventCode();
    }

    public function getTemplateIdentifier(): array
    {
        return NotificationMappingLoader::forEventCode($this->getEventCode());
    }

    public function getVariables(): array
    {
        return [
            'owner_name' => $this->data['owner_name'] ?? 'Restaurant Owner',
            'document_type' => $this->data['document_type'] ?? 'Compliance document',
            'valid_from' => $this->data['valid_from'] ?? '',
            'expires_at' => $this->data['expires_at'] ?? '',
            'validity_period' => $this->data['validity_period'] ?? '',
            'company_name' => $this->data['company_name'] ?? config('app.name'),
            'current_year' => $this->data['current_year'] ?? date('Y'),
        ];
    }

    public function getAvailableRecipients(): array
    {
        return [
            'notify_owner' => 'Notify restaurant owner',
            'notify_admin' => 'Notify admin',
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
            'notify_owner' => \Modules\Acl\Models\Admin::class,
            'notify_admin' => \Modules\Acl\Models\Admin::class,
        ];
    }

    public function getAvailableVariables(): array
    {
        return [
            'owner_name' => 'Restaurant Owner name',
            'document_type' => 'Document type',
            'valid_from' => 'Valid from date',
            'expires_at' => 'Expiry date',
            'validity_period' => 'Validity period label',
        ];
    }

    public function toArray($notifiable = null): array
    {
        return [
            'title' => $this->defaultTitle(),
            'message' => $this->defaultMessage(),
            'data' => $this->getVariables(),
        ];
    }
}
