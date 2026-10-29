<?php

namespace Modules\Tax\Enums;

enum TaxApplicationLevel: string
{
    case FOOD_ITEM = 'food_item';
    case SUBTOTAL = 'subtotal';

    public function label(): string
    {
        return match($this) {
            self::FOOD_ITEM => 'Food Item',
            self::SUBTOTAL => 'Subtotal',
        };
    }

    public function description(): string
    {
        return match($this) {
            self::FOOD_ITEM => 'Tax is calculated and applied to each individual food item',
            self::SUBTOTAL => 'Tax is calculated and applied to the order subtotal',
        };
    }
}