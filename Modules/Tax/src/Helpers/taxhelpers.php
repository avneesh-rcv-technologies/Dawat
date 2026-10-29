<?php

use Modules\Tax\Services\FoodItemTaxService;
use Modules\Tax\Enums\TaxCalculationType;
use Modules\Tax\Enums\TaxApplicationLevel;
use Modules\Tax\Models\TaxCategory;
use Modules\FoodManagements\Models\FoodItem;

if (!function_exists('fn_calculate_food_item_tax')) {
    /**
     * Calculate tax for a food item based on current settings
     */
    function fn_calculate_food_item_tax(FoodItem $foodItem, ?array $customTaxCategoryIds = null): array
    {
        $taxService = app(FoodItemTaxService::class);
        return $taxService->calculateDisplayPrice($foodItem, $customTaxCategoryIds);
    }
}

if (!function_exists('fn_get_food_item_display_price')) {
    /**
     * Get the display price for a food item (with tax applied based on settings)
     */
    function fn_get_food_item_display_price(FoodItem $foodItem): float
    {
        $calculation = fn_calculate_food_item_tax($foodItem);
        return $calculation['display_price'];
    }
}

if (!function_exists('fn_is_tax_inclusive')) {
    /**
     * Check if tax calculation is set to inclusive
     */
    function fn_is_tax_inclusive(): bool
    {
        $calculationType = fn_get_setting('tax.calculation_type', TaxCalculationType::EXCLUSIVE->value);
        return $calculationType === TaxCalculationType::INCLUSIVE->value;
    }
}

if (!function_exists('fn_get_tax_calculation_type')) {
    /**
     * Get the current tax calculation type
     */
    function fn_get_tax_calculation_type(): TaxCalculationType
    {
        $type = fn_get_setting('tax.calculation_type', TaxCalculationType::EXCLUSIVE->value);
        return TaxCalculationType::from($type);
    }
}

if (!function_exists('fn_get_tax_application_level')) {
    /**
     * Get the current tax application level
     */
    function fn_get_tax_application_level(): TaxApplicationLevel
    {
        $level = fn_get_setting('tax.application_level', TaxApplicationLevel::FOOD_ITEM->value);
        return TaxApplicationLevel::from($level);
    }
}

if (!function_exists('fn_is_item_level_tax')) {
    /**
     * Check if tax is applied at item level
     */
    function fn_is_item_level_tax(): bool
    {
        $applicationLevel = fn_get_setting('tax.application_level', TaxApplicationLevel::FOOD_ITEM->value);
        return $applicationLevel === TaxApplicationLevel::FOOD_ITEM->value;
    }
}

if (!function_exists('fn_is_subtotal_level_tax')) {
    /**
     * Check if tax is applied at subtotal level
     */
    function fn_is_subtotal_level_tax(): bool
    {
        $applicationLevel = fn_get_setting('tax.application_level', TaxApplicationLevel::FOOD_ITEM->value);
        return $applicationLevel === TaxApplicationLevel::SUBTOTAL->value;
    }
}

if (!function_exists('fn_calculate_cart_tax')) {
    /**
     * Calculate tax for multiple food items in cart
     */
    function fn_calculate_cart_tax(array $items): array
    {
        $taxService = app(FoodItemTaxService::class);
        return $taxService->calculateCartTax($items);
    }
}

if (!function_exists('fn_calculate_subtotal_tax')) {
    /**
     * Calculate tax for subtotal
     */
    function fn_calculate_subtotal_tax(float $subtotal, ?array $customTaxCategoryIds = null): array
    {
        $taxService = app(FoodItemTaxService::class);
        return $taxService->calculateSubtotalTax($subtotal, $customTaxCategoryIds);
    }
}

if (!function_exists('fn_get_tax_breakdown')) {
    /**
     * Get detailed tax breakdown for a food item
     */
    function fn_get_tax_breakdown(FoodItem $foodItem, ?array $customTaxCategoryIds = null): array
    {
        $taxService = app(FoodItemTaxService::class);
        return $taxService->getTaxBreakdown($foodItem, $customTaxCategoryIds);
    }
}

if (!function_exists('fn_format_price_with_tax_info')) {
    /**
     * Format price with tax information for display
     */
    function fn_format_price_with_tax_info(FoodItem $foodItem, ?string $currency = null): string
    {
        $calculation = fn_calculate_food_item_tax($foodItem);
        $currency = $currency ?? fn_get_setting('general.currency', 'USD');
        
        // If tax is applied at subtotal level, show base price only
        if (!$calculation['is_item_level_tax']) {
            return number_format($calculation['base_price'], 2) . ' ' . $currency . 
                   ' <small class="text-gray-500">(tax calculated at checkout)</small>';
        }
        
        if ($calculation['tax_rate'] <= 0) {
            return number_format($calculation['display_price'], 2) . ' ' . $currency;
        }
        
        $taxCategoriesText = $calculation['tax_category_names_string'] ?: 'Tax';
        
        if ($calculation['is_tax_inclusive']) {
            return number_format($calculation['display_price'], 2) . ' ' . $currency . 
                   ' <small class="text-gray-500">(incl. ' . $taxCategoriesText . ')</small>';
        } else {
            return number_format($calculation['base_price'], 2) . ' ' . $currency . 
                   ' <small class="text-gray-500">(+ ' . $taxCategoriesText . ' = ' . 
                   number_format($calculation['display_price'], 2) . ' ' . $currency . ')</small>';
        }
    }
}

if (!function_exists('fn_get_available_tax_categories')) {
    /**
     * Get available tax categories for dropdown selection
     */
    function fn_get_available_tax_categories(): array
    {
        $taxService = app(FoodItemTaxService::class);
        return $taxService->getAvailableTaxCategories();
    }
}

if (!function_exists('fn_get_default_tax_categories')) {
    /**
     * Get the default tax categories
     */
    function fn_get_default_tax_categories(): array
    {
        $categoryIds = fn_get_setting('tax.default_category_ids', []);
        
        if (is_string($categoryIds)) {
            $categoryIds = json_decode($categoryIds, true) ?: [];
        }
        
        if (!is_array($categoryIds)) {
            $categoryIds = [];
        }
        
        $categoryIds = array_filter($categoryIds, 'is_numeric');
        
        return TaxCategory::whereIn('id', $categoryIds)->get()->toArray();
    }
}

if (!function_exists('fn_get_default_tax_category_ids')) {
    /**
     * Get the default tax category IDs
     */
    function fn_get_default_tax_category_ids(): array
    {
        $categoryIds = fn_get_setting('tax.default_category_ids', []);
        
        if (is_string($categoryIds)) {
            $categoryIds = json_decode($categoryIds, true) ?: [];
        }
        
        if (!is_array($categoryIds)) {
            $categoryIds = [];
        }
        
        return array_filter($categoryIds, 'is_numeric');
    }
}