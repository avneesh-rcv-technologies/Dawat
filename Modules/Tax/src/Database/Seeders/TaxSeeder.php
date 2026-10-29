<?php

namespace Modules\Tax\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Tax\Models\TaxRate;
use Modules\Countries\Models\Country;
use Modules\Tax\Enums\TaxType;
use Modules\Tax\Models\TaxCategory;

class TaxSeeder extends Seeder
{
    public function run()
    {
        $us = Country::where('code', 'US')->first();
        $uk = Country::where('code', 'GB')->first();

        // Create tax categories
        $standardCategory = TaxCategory::updateOrCreate(['name' => 'Standard'], [
            'priority' => 1,
            'status' => true
        ]);

        $reducedCategory = TaxCategory::updateOrCreate(['name' => 'Reduced'], [
            'priority' => 2,
            'status' => true
        ]);

        // Only seed rates for countries available in this installation.
        if ($us) {
            $usStandardRate = TaxRate::updateOrCreate(
                ['name' => 'US Standard Rate', 'country_id' => $us->id],
                [
                    'rate_value' => 7.25,
                    'type' => TaxType::PERCENTAGE,
                    'is_active' => true,
                    'priority' => 1,
                ]
            );

            $usCaliforniaRate = TaxRate::updateOrCreate(
                ['name' => 'California Rate', 'country_id' => $us->id, 'state' => 'CA'],
                [
                    'rate_value' => 7.25,
                    'type' => TaxType::PERCENTAGE,
                    'is_active' => true,
                    'priority' => 2,
                ]
            );

            $standardCategory->taxRates()->syncWithoutDetaching([
                $usStandardRate->id => ['priority' => 1],
                $usCaliforniaRate->id => ['priority' => 2],
            ]);
        }

        if ($uk) {
            $ukStandardRate = TaxRate::updateOrCreate(
                ['name' => 'UK VAT Standard', 'country_id' => $uk->id],
                [
                    'rate_value' => 20.0,
                    'type' => TaxType::PERCENTAGE,
                    'is_active' => true,
                    'priority' => 1,
                ]
            );

            $ukReducedRate = TaxRate::updateOrCreate(
                ['name' => 'UK VAT Reduced', 'country_id' => $uk->id],
                [
                    'rate_value' => 5.0,
                    'type' => TaxType::PERCENTAGE,
                    'is_active' => true,
                    'priority' => 2,
                ]
            );

            $standardCategory->taxRates()->syncWithoutDetaching([
                $ukStandardRate->id => ['priority' => 1],
            ]);

            $reducedCategory->taxRates()->syncWithoutDetaching([
                $ukReducedRate->id => ['priority' => 1],
            ]);
        }
    }
}
