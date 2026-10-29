<?php

namespace Modules\Tax\Enums;

enum TaxCalculationType: string
{
    case INCLUSIVE = 'inclusive';
    case EXCLUSIVE = 'exclusive';

    public function label(): string
    {
        return match($this) {
            self::INCLUSIVE => 'Inclusive (Tax included in price)',
            self::EXCLUSIVE => 'Exclusive (Tax added to price)',
        };
    }

    public function description(): string
    {
        return match($this) {
            self::INCLUSIVE => 'Tax is already included in the food item price',
            self::EXCLUSIVE => 'Tax will be added on top of the food item price',
        };
    }
}