<?php

namespace Modules\Discounts\Enums;

enum DiscountType: string
{
    case FIXED = 'F';
    case PERCENTAGE = 'P';

    public function label(): string
    {
        return match($this) {
            self::FIXED => 'Fixed Amount',
            self::PERCENTAGE => 'Percentage'
        };
    }
}