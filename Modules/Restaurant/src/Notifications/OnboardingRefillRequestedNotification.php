<?php

namespace Modules\Restaurant\Notifications;

use Modules\Acl\Models\Admin;

class OnboardingRefillRequestedNotification extends RestaurantBankingSubmittedNotification
{
    protected const EVENT_CODE = 'onboarding_refill_requested';

    public function getVariables(): array
    {
        return [
            'restaurant_name' => e($this->data['restaurant_name'] ?? 'Restaurant'),
            'restaurant_id' => (int) ($this->data['restaurant_id'] ?? 0),
            'owner_name' => e($this->data['owner_name'] ?? 'Restaurant owner'),
            'step_name' => e($this->data['step_name'] ?? 'Onboarding'),
            'document_name' => e($this->data['document_name'] ?? 'All details in this step'),
            'reason' => e($this->data['reason'] ?? ''),
            'requested_at' => e($this->data['requested_at'] ?? now()->format('M d, Y h:i A')),
            'correction_url' => e($this->data['correction_url'] ?? route('restaurant.onboarding.kyc')),
            'app_name' => e(config('app.name', 'Dawat')),
            'current_year' => now()->year,
        ];
    }

    public function getAvailableVariables(): array
    {
        return [
            'restaurant_name' => 'Restaurant name', 'restaurant_id' => 'Restaurant ID',
            'owner_name' => 'Restaurant owner name', 'step_name' => 'Onboarding step requiring a refill',
            'document_name' => 'Document or details to update', 'reason' => 'Reason and required corrections',
            'requested_at' => 'Time the refill was requested', 'correction_url' => 'Link to update the requested onboarding step',
            'app_name' => 'Application name', 'current_year' => 'Current year',
        ];
    }

    public function getAvailableRecipients(): array
    {
        return ['notify_restaurant' => 'Notify Restaurant Owner'];
    }

    public function getRecipientTypeMapping(): array
    {
        return ['notify_restaurant' => 'notifiable'];
    }

    public function getRecipientClassMapping(): array
    {
        return ['notify_restaurant' => Admin::class];
    }
}
