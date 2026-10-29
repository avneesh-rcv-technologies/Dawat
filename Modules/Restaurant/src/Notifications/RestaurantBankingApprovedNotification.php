<?php

namespace Modules\Restaurant\Notifications;

class RestaurantBankingApprovedNotification extends RestaurantBankingSubmittedNotification
{
    protected const EVENT_CODE = 'restaurant_banking_approved';

    public function getVariables(): array
    {
        return array_merge(parent::getVariables(), [
            'reviewed_at' => $this->data['reviewed_at'] ?? '',
            'rejection_reason' => e($this->data['rejection_reason'] ?? ''),
        ]);
    }

    public function getAvailableVariables(): array
    {
        return array_merge(parent::getAvailableVariables(), [
            'reviewed_at' => 'Review time', 'rejection_reason' => 'Reason banking details were rejected',
        ]);
    }
}
