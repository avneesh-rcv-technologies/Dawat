<?php

namespace Modules\Restaurant\Notifications;

class RestaurantTaxApprovedNotification extends RestaurantTaxSubmittedNotification
{
    protected const EVENT_CODE = 'restaurant_tax_approved';

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
            'reviewed_at' => 'Review time', 'rejection_reason' => 'Reason tax information were rejected',
        ]);
    }
}
