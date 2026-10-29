<?php

namespace Modules\Tax\Enums;

enum TaxType: string
{
    case PERCENTAGE = 'P';
    case FIXED = 'F';

    public function label(): string
    {
        return match($this) {
            self::PERCENTAGE => 'Percentage',
            self::FIXED => 'Fixed Amount',
        };
    }
}