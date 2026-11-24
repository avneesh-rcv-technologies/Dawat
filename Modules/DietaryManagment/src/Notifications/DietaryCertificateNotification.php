<?php

namespace Modules\DietaryManagment\Notifications;

use Modules\Acl\Models\Admin;
use Modules\Notifications\Contracts\Notification;
use Modules\Notifications\Support\NotificationMappingLoader;

abstract class DietaryCertificateNotification implements Notification
{
    public const EVENTS = [
        'submitted' => 'Submitted', 'resubmitted' => 'Resubmitted', 'under_review' => 'UnderReview',
        'approved' => 'Approved', 'rejected' => 'Rejected', 'changes_requested' => 'ChangesRequested',
    ];

    protected const ACTION = 'submitted';

    public function __construct(protected array $data = []) {}

    public function getEventCode(): string
    {
        return 'dietary_certificate_'.static::ACTION;
    }

    public function getTemplateIdentifier(): array
    {
        return NotificationMappingLoader::forEventCode($this->getEventCode());
    }

    public function getVariables(): array
    {
        return [
            'restaurant_name' => e($this->data['restaurant_name'] ?? 'Restaurant'),
            'owner_name' => e($this->data['owner_name'] ?? 'Restaurant owner'),
            'certificate_name' => e($this->data['certificate_name'] ?? 'Dietary certificate'),
            'dietary_label' => e($this->data['dietary_label'] ?? ''),
            'file_name' => e($this->data['file_name'] ?? 'Certificate document'),
            'status_label' => ucfirst(str_replace('_', ' ', static::ACTION)),
            'submitted_at' => e($this->data['submitted_at'] ?? 'Not recorded'),
            'reviewed_at' => e($this->data['reviewed_at'] ?? 'Not reviewed yet'),
            'admin_notes' => e(($this->data['admin_notes'] ?? '') ?: 'No review notes.'),
            'action_url' => route('dashboard.index'),
            'support_url' => route('website.contact'), 'current_year' => now()->year,
        ];
    }

    public function getAvailableVariables(): array
    {
        return [
            'restaurant_name' => 'Restaurant name', 'owner_name' => 'Restaurant owner name',
            'certificate_name' => 'Certification type name', 'dietary_label' => 'Dietary label',
            'file_name' => 'Original uploaded file name', 'status_label' => 'Certificate event status',
            'submitted_at' => 'Recorded submission time', 'reviewed_at' => 'Recorded review time',
            'admin_notes' => 'Review feedback', 'action_url' => 'Account dashboard URL',
            'support_url' => 'Contact support URL', 'current_year' => 'Current year',
        ];
    }

    public function getPushVariables(): array
    {
        return array_map(fn ($value) => is_string($value) ? html_entity_decode($value, ENT_QUOTES, 'UTF-8') : $value, $this->getVariables());
    }

    public function getAvailableRecipients(): array
    {
        return ['notify_restaurant' => 'Notify Restaurant Owner', 'notify_admin' => 'Notify Admin'];
    }

    public function getRecipientTypeMapping(): array
    {
        return ['notify_restaurant' => 'notifiable', 'notify_admin' => 'admin'];
    }

    public function getRecipientClassMapping(): array
    {
        return ['notify_restaurant' => Admin::class, 'notify_admin' => Admin::class];
    }

    public function toArray(): array
    {
        return ['event_code' => $this->getEventCode(), 'template' => $this->getTemplateIdentifier(),
            'variables' => $this->getVariables(), 'available_variables' => $this->getAvailableVariables()];
    }
}
