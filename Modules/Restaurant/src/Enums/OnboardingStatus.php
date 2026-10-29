<?php

declare(strict_types=1);

namespace Modules\Restaurant\Enums;

enum OnboardingStatus: string
{
    case DRAFT = 'draft';
    case SUBMITTED = 'submitted';
    case UNDER_REVIEW = 'under_review';
    case ACTION_REQUIRED = 'action_required';
    case APPROVED = 'approved';
    case ACTIVE = 'active';
    case REJECTED = 'rejected';
    case SUSPENDED = 'suspended';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Draft',
            self::SUBMITTED => 'Submitted',
            self::UNDER_REVIEW => 'Under Review',
            self::ACTION_REQUIRED => 'Action Required',
            self::APPROVED => 'Approved',
            self::ACTIVE => 'Active',
            self::REJECTED => 'Rejected',
            self::SUSPENDED => 'Suspended',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::DRAFT => 'bg-gray-100 text-gray-800',
            self::SUBMITTED => 'bg-yellow-100 text-yellow-800',
            self::UNDER_REVIEW => 'bg-blue-100 text-blue-800',
            self::ACTION_REQUIRED => 'bg-orange-100 text-orange-800',
            self::APPROVED => 'bg-teal-100 text-teal-800',
            self::ACTIVE => 'bg-green-100 text-green-800',
            self::REJECTED => 'bg-red-100 text-red-800',
            self::SUSPENDED => 'bg-purple-100 text-purple-800',
        };
    }
}
