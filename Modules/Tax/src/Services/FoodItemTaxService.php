<?php

namespace Modules\Tax\Services;

use Modules\Tax\Enums\TaxCalculationType;
use Modules\Tax\Enums\TaxApplicationLevel;
use Modules\Tax\Models\TaxCategory;
use Modules\FoodManagements\Models\FoodItem;

class FoodItemTaxService
{
    /**
     * Calculate the display price for a food item based on tax settings
     */
    public function calculateDisplayPrice(FoodItem $foodItem, ?array $customTaxCategoryIds = null): array
    {
        $calculationType = fn_get_setting('tax.calculation_type', TaxCalculationType::EXCLUSIVE->value);
        $applicationLevel = fn_get_setting('tax.application_level', TaxApplicationLevel::FOOD_ITEM->value);
        $taxCategoryIds = $customTaxCategoryIds ?? $this->getDefaultTaxCategoryIds();
        
        $basePrice = $foodItem->sale_price;
        $totalTaxAmount = 0;
        $displayPrice = $basePrice;
        $totalTaxRate = 0;
        $taxCategoryNames = [];
        $taxBreakdown = [];
        
        // Only calculate tax if application level is food_item and we have categories
        if ($applicationLevel === TaxApplicationLevel::FOOD_ITEM->value && !empty($taxCategoryIds)) {
            foreach ($taxCategoryIds as $taxCategoryId) {
                $taxCategory = TaxCategory::find($taxCategoryId);
                if ($taxCategory) {
                    $taxAmount = $taxCategory->calculateTax($basePrice);
                    $taxRate = $basePrice > 0 ? ($taxAmount / $basePrice) * 100 : 0;
                    
                    $totalTaxAmount += $taxAmount;
                    $totalTaxRate += $taxRate;
                    $taxCategoryNames[] = $taxCategory->name;
                    
                    $taxBreakdown[] = [
                        'category_id' => $taxCategory->id,
                        'category_name' => $taxCategory->name,
                        'tax_amount' => round($taxAmount, 2),
                        'tax_rate' => round($taxRate, 2),
                    ];
                }
            }
            
            if ($calculationType === TaxCalculationType::INCLUSIVE->value) {
                // Tax is included in the price - extract tax amount
                $totalRate = 100 + $totalTaxRate;
                if ($totalRate > 0) {
                    $totalTaxAmount = $basePrice * ($totalTaxRate / $totalRate);
                    $priceWithoutTax = $basePrice - $totalTaxAmount;
                    $displayPrice = $basePrice; // Display price remains the same
                }
            } else {
                // Tax is exclusive - add tax to price
                $priceWithoutTax = $basePrice;
                $displayPrice = $basePrice + $totalTaxAmount;
            }
        }
        
        if (!isset($priceWithoutTax)) {
            $priceWithoutTax = $basePrice;
        }

        return [
            'base_price' => round($basePrice, 2),
            'price_without_tax' => round($priceWithoutTax, 2),
            'tax_amount' => round($totalTaxAmount, 2),
            'display_price' => round($displayPrice, 2),
            'tax_rate' => round($totalTaxRate, 2),
            'tax_category_ids' => $taxCategoryIds,
            'tax_category_names' => $taxCategoryNames,
            'tax_category_names_string' => implode(', ', $taxCategoryNames),
            'tax_breakdown' => $taxBreakdown,
            'calculation_type' => $calculationType,
            'application_level' => $applicationLevel,
            'is_tax_inclusive' => $calculationType === TaxCalculationType::INCLUSIVE->value,
            'is_item_level_tax' => $applicationLevel === TaxApplicationLevel::FOOD_ITEM->value,
        ];
    }

    /**
     * Calculate tax for multiple food items (for cart/order)
     */
    public function calculateCartTax(array $items): array
    {
        $applicationLevel = fn_get_setting('tax.application_level', TaxApplicationLevel::FOOD_ITEM->value);
        $calculationType = fn_get_setting('tax.calculation_type', TaxCalculationType::EXCLUSIVE->value);
        $taxCategoryIds = $this->getDefaultTaxCategoryIds();
        
        $totalTax = 0;
        $totalBasePrice = 0;
        $totalDisplayPrice = 0;
        $itemsWithTax = [];

        foreach ($items as $item) {
            $foodItem = $item['food_item'] ?? null;
            $quantity = $item['quantity'] ?? 1;
            $customTaxCategoryIds = $item['tax_category_ids'] ?? null;
            
            if (!$foodItem instanceof FoodItem) {
                continue;
            }

            $taxCalculation = $this->calculateDisplayPrice($foodItem, $customTaxCategoryIds);
            
            $itemTotal = [
                'food_item_id' => $foodItem->id,
                'quantity' => $quantity,
                'unit_base_price' => $taxCalculation['base_price'],
                'unit_display_price' => $taxCalculation['display_price'],
                'unit_tax_amount' => $taxCalculation['tax_amount'],
                'line_base_price' => $taxCalculation['base_price'] * $quantity,
                'line_display_price' => $taxCalculation['display_price'] * $quantity,
                'line_tax_amount' => $taxCalculation['tax_amount'] * $quantity,
                'tax_rate' => $taxCalculation['tax_rate'],
                'tax_category_ids' => $taxCalculation['tax_category_ids'],
                'tax_category_names' => $taxCalculation['tax_category_names'],
                'tax_breakdown' => $taxCalculation['tax_breakdown'],
                'is_tax_inclusive' => $taxCalculation['is_tax_inclusive'],
                'is_item_level_tax' => $taxCalculation['is_item_level_tax'],
            ];

            $itemsWithTax[] = $itemTotal;
            $totalBasePrice += $itemTotal['line_base_price'];
            
            if ($applicationLevel === TaxApplicationLevel::FOOD_ITEM->value) {
                // Item-level tax: use individual item calculations
                $totalTax += $itemTotal['line_tax_amount'];
                $totalDisplayPrice += $itemTotal['line_display_price'];
            } else {
                // Subtotal-level tax: items don't have tax, calculate on subtotal
                $totalDisplayPrice += $itemTotal['line_base_price'];
            }
        }

        // Calculate subtotal-level tax if applicable
        if ($applicationLevel === TaxApplicationLevel::SUBTOTAL->value && !empty($taxCategoryIds)) {
            $subtotalTaxCalculation = $this->calculateSubtotalTax($totalBasePrice, $taxCategoryIds);
            $totalTax = $subtotalTaxCalculation['tax_amount'];
            $totalDisplayPrice = $subtotalTaxCalculation['display_total'];
        }

        return [
            'items' => $itemsWithTax,
            'total_base_price' => round($totalBasePrice, 2),
            'total_display_price' => round($totalDisplayPrice, 2),
            'total_tax_amount' => round($totalTax, 2),
            'calculation_type' => $calculationType,
            'application_level' => $applicationLevel,
            'is_subtotal_tax' => $applicationLevel === TaxApplicationLevel::SUBTOTAL->value,
        ];
    }

    /**
     * Calculate subtotal tax specifically
     */
    public function calculateSubtotalTax(float $subtotal, ?array $customTaxCategoryIds = null): array
    {
        $calculationType = fn_get_setting('tax.calculation_type', TaxCalculationType::EXCLUSIVE->value);
        $taxCategoryIds = $customTaxCategoryIds ?? $this->getDefaultTaxCategoryIds();
        
        $totalTaxAmount = 0;
        $displayTotal = $subtotal;
        $totalTaxRate = 0;
        $taxCategoryNames = [];
        $taxBreakdown = [];
        
        if (!empty($taxCategoryIds)) {
            foreach ($taxCategoryIds as $taxCategoryId) {
                $taxCategory = TaxCategory::find($taxCategoryId);
                if ($taxCategory) {
                    $taxAmount = $taxCategory->calculateTax($subtotal);
                    $taxRate = $subtotal > 0 ? ($taxAmount / $subtotal) * 100 : 0;
                    
                    $totalTaxAmount += $taxAmount;
                    $totalTaxRate += $taxRate;
                    $taxCategoryNames[] = $taxCategory->name;
                    
                    $taxBreakdown[] = [
                        'category_id' => $taxCategory->id,
                        'category_name' => $taxCategory->name,
                        'tax_amount' => round($taxAmount, 2),
                        'tax_rate' => round($taxRate, 2),
                    ];
                }
            }
            
            if ($calculationType === TaxCalculationType::INCLUSIVE->value) {
                // Tax is included in the subtotal
                $totalRate = 100 + $totalTaxRate;
                if ($totalRate > 0) {
                    $totalTaxAmount = $subtotal * ($totalTaxRate / $totalRate);
                    $subtotalWithoutTax = $subtotal - $totalTaxAmount;
                    $displayTotal = $subtotal; // Display total remains the same
                }
            } else {
                // Tax is exclusive - add tax to subtotal
                $subtotalWithoutTax = $subtotal;
                $displayTotal = $subtotal + $totalTaxAmount;
            }
        }
        
        if (!isset($subtotalWithoutTax)) {
            $subtotalWithoutTax = $subtotal;
        }

        return [
            'subtotal' => round($subtotal, 2),
            'subtotal_without_tax' => round($subtotalWithoutTax, 2),
            'tax_amount' => round($totalTaxAmount, 2),
            'display_total' => round($displayTotal, 2),
            'tax_rate' => round($totalTaxRate, 2),
            'tax_category_ids' => $taxCategoryIds,
            'tax_category_names' => $taxCategoryNames,
            'tax_category_names_string' => implode(', ', $taxCategoryNames),
            'tax_breakdown' => $taxBreakdown,
            'calculation_type' => $calculationType,
            'is_tax_inclusive' => $calculationType === TaxCalculationType::INCLUSIVE->value,
        ];
    }

    /**
     * Get tax breakdown for display purposes
     */
    public function getTaxBreakdown(FoodItem $foodItem, ?array $customTaxCategoryIds = null): array
    {
        $calculation = $this->calculateDisplayPrice($foodItem, $customTaxCategoryIds);
        $applicationLevel = fn_get_setting('tax.application_level', TaxApplicationLevel::FOOD_ITEM->value);
        
        $description = '';
        if ($applicationLevel === TaxApplicationLevel::SUBTOTAL->value) {
            $description = "Tax will be calculated at subtotal level, not per item";
        } else {
            $taxCategoriesText = $calculation['tax_category_names_string'] ?: 'No Tax Categories';
            $description = $calculation['is_tax_inclusive'] 
                ? "Tax from {$taxCategoriesText} ({$calculation['tax_rate']}%) is included in the price"
                : "Tax from {$taxCategoriesText} ({$calculation['tax_rate']}%) will be added to the price";
        }
        
        return [
            'item_name' => $foodItem->name,
            'base_price' => $calculation['base_price'],
            'tax_rate' => $calculation['tax_rate'] . '%',
            'tax_amount' => $calculation['tax_amount'],
            'final_price' => $calculation['display_price'],
            'tax_category_names' => $calculation['tax_category_names'],
            'tax_category_names_string' => $calculation['tax_category_names_string'],
            'tax_breakdown' => $calculation['tax_breakdown'],
            'calculation_method' => $calculation['is_tax_inclusive'] ? 'Tax Inclusive' : 'Tax Exclusive',
            'application_level' => $applicationLevel === TaxApplicationLevel::FOOD_ITEM->value ? 'Item Level' : 'Subtotal Level',
            'description' => $description
        ];
    }

    /**
     * Get default tax category IDs from settings
     */
    protected function getDefaultTaxCategoryIds(): array
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

    /**
     * Get available tax categories for dropdown
     */
    public function getAvailableTaxCategories(): array
    {
        return TaxCategory::active()
            ->orderBy('priority', 'desc')
            ->orderBy('name')
            ->get()
            ->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'description' => $category->description,
                    'formatted_name' => $category->name . ($category->description ? ' - ' . $category->description : ''),
                ];
            })
            ->toArray();
    }
}