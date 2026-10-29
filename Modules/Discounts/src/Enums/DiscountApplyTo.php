<?php

namespace Modules\Discounts\Enums;

enum DiscountApplyTo: string
{
    case SUBTOTAL   = 'subtotal';
    case TOTAL      = 'total';

    public function label(): string
    {
        return match($this) {
            self::SUBTOTAL => 'Subtotal',
            self::TOTAL => 'Total'
        };
    }
}