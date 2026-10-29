<?php

namespace Modules\Tax\Services;

use Modules\Tax\Enums\TaxType;
use Modules\Tax\Models\TaxRate;
use Modules\Acl\Models\Customer;
use Modules\Catalog\Models\Product;
use Illuminate\Support\Facades\Cache;
use Modules\FoodManagements\Models\FoodItem;

class TaxService
{
    /**
     * Calculate tax for order items with product-specific tax rates
     */
    public function calculate(
        array $items,
        array $shippingAddress,
        array $billingAddress,
        Customer $customer
    ): float {
        $taxTotal = 0;
        $taxAddress = $this->getTaxableAddress($shippingAddress, $billingAddress);

        foreach ($items as $item) {
            $foodItem = FoodItem::find($item['food_item_id']);
            $itemTotal = $item['total'] ?? ($item['price'] * $item['quantity']);

            $taxTotal += $this->calculateItemTax(
                $itemTotal,
                $taxAddress,
                $customer,
                $foodItem->tax_id ?? null
            );
        }

        // Calculate shipping tax if applicable
        if (fn_get_setting('tax.tax_shipping')) {
            $taxTotal += $this->calculateItemTax(
                $this->getShippingAmount($items, $shippingAddress),
                $taxAddress,
                $customer,
                null // Shipping typically uses default tax rates
            );
        }

        return round($taxTotal, 2);
    }

    /**
     * Calculate tax for a single item with product-specific tax rate
     */
    protected function calculateItemTax(
        float $amount,
        array $address,
        Customer $customer,
        ?int $taxCategoryId = null
    ): float {
        if ($this->isTaxExempt($customer)) {
            return 0;
        }

        // Get all applicable tax rates from the category (federal + state + city)
        $taxRates = $this->getTaxRatesForCategory($taxCategoryId, $address);

        return $taxRates->reduce(function ($carry, $rate) use ($amount) {
            return $carry + $this->calculateRateTax($rate, $amount);
        }, 0);
    }

    protected function getTaxRatesForCategory(?int $taxCategoryId, array $address)
    {
        if (!$taxCategoryId) {
            return collect();
        }

        $cacheKey = "tax_category_rates_{$taxCategoryId}_" .
            ($address['country'] ?? 'any') . "_" .
            ($address['state'] ?? 'any');

        // return Cache::remember($cacheKey, now()->addDay(), function () use ($taxCategoryId, $address) {
        return TaxRate::query()
            ->whereHas('taxCategories', function ($q) use ($taxCategoryId) {
                $q->where('tax_category_id', $taxCategoryId);
            })
            ->where('country_id', $address['country'] ?? 0) 
            ->when(!empty($address['state']), function ($q) use ($address) {
                $state = $address['state'];
                $q->where(function ($qq) use ($state) {
                    $qq->where('state', $state)->orWhereNull('state');
                });
            })
            ->where('is_active', true)
            ->orderBy('priority', 'desc')
            ->get();
        // });
    }

    /**
     * Calculate tax for a single rate
     */
    protected function calculateRateTax(TaxRate $rate, float $amount): float
    {
        return match ($rate->type) {
            TaxType::FIXED => $rate->rate_value,
            TaxType::PERCENTAGE => ($amount * $rate->rate_value) / 100,
            default => 0
        };
    }

    /**
     * Determine which address to use for tax calculation
     */
    protected function getTaxableAddress(array $shippingAddress, array $billingAddress): array
    {
        // return (fn_get_setting('general.tax_applicable') == 'billing')
            return $billingAddress;
            // : $shippingAddress;
    }

    /**
     * Check if customer is tax exempt
     */
    protected function isTaxExempt(Customer $customer): bool
    {
        // Adapt to Customer model structure - check if these properties exist
        return ($customer->tax_exempt ?? false) ||
            (isset($customer->group) && ($customer->group->tax_exempt ?? false));
    }

    /**
     * Get shipping amount
     */
    protected function getShippingAmount(array $items, array $address): float
    {
        return 0.0; // Implement your shipping calculation logic
    }

    /**
     * Create or update a tax rate
     */
    public function saveTaxRate(array $data): TaxRate
    {
        $taxRate = isset($data['id'])
            ? TaxRate::findOrFail($data['id'])
            : new TaxRate();

        $taxRate->fill($data);
        $taxRate->save();

        $this->clearTaxRateCache();

        return $taxRate;
    }

    /**
     * Clear tax rate caches
     */
    public function clearTaxRateCache(): void
    {
        Cache::tags(['tax_rates'])->flush();
    }

    public function getProductTaxRates(?int $taxCategoryId, $address)
    {
        return $this->getTaxRatesForCategory($taxCategoryId, $address);
    }

    public function calculateRateTaxFromOrder($rate, $amount)
    {
        return $this->calculateRateTax($rate, $amount);
    }
    /**
     * Calculate order tax depending on `general.line_item_type` setting.
     * - 'L' => tax per line item (existing behavior)
     * - 'S' => tax on order subtotal (new behavior)
     */
    public function calculateOrderTax(
        array $items,
        array $shippingAddress,
        array $billingAddress,
        Customer $customer
    ): float {
        $mode = fn_get_setting('general.line_item_type') ?? 'L';

        if (strtoupper($mode) === 'S') {
            return $this->calculateSubtotalTax($items, $shippingAddress, $billingAddress, $customer);
        }

        return $this->calculate($items, $shippingAddress, $billingAddress, $customer);
    }

    /**
     * Calculate tax on the order subtotal.
     * This gathers all tax rates for product tax categories present on the
     * order and applies those rates against the subtotal amount.
     */
    protected function calculateSubtotalTax(
        array $items,
        array $shippingAddress,
        array $billingAddress,
        Customer $customer
    ): float {
        // if ($this->isTaxExempt($customer)) {
        //     return 0;
        // }

        $taxAddress = $this->getTaxableAddress($shippingAddress, $billingAddress);

        // Calculate subtotal (prefer total if present, otherwise calculate from price * quantity)
        $subtotal = array_reduce($items, function ($carry, $item) {
            $carry += $item['total'] ?? (($item['price'] ?? 0) * ($item['quantity'] ?? 1));
            return $carry;
        }, 0.0);

        if ($subtotal <= 0) {
            return 0.0;
        }

        // Collect unique tax rates across all item tax categories
        $rateCollection = collect();
        foreach ($items as $item) {
            $foodItem = FoodItem::find($item['food_item_id']);
            $taxCategoryId = $foodItem->tax_id ?? null;
            if ($taxCategoryId) {
                $rates = $this->getTaxRatesForCategory($taxCategoryId, $taxAddress);
                foreach ($rates as $r) {
                    if (!$rateCollection->contains('id', $r->id)) {
                        $rateCollection->push($r);
                    }
                }
            }
        }

        // If no product-specific rates found, return 0 (no default global rates applied)
        if ($rateCollection->isEmpty()) {
            return 0.0;
        }

        $taxTotal = $rateCollection->reduce(function ($carry, $rate) use ($subtotal) {
            return $carry + $this->calculateRateTax($rate, $subtotal);
        }, 0.0);

        return round($taxTotal, 2);
    }
}
