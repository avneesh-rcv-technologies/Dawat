<?php

use Illuminate\Database\Migrations\Migration;
use Modules\GeneralSetting\Models\Setting;
use Modules\Tax\Enums\TaxCalculationType;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add default tax configuration settings
        $settings = [
            [
                'key' => 'tax.calculation_type',
                'value' => TaxCalculationType::EXCLUSIVE->value,
            ],
            [
                'key' => 'tax.default_category_id',
                'value' => null,
            ],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                ['value' => $setting['value']]
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Setting::whereIn('key', [
            'tax.calculation_type',
            'tax.default_category_id',
        ])->delete();
    }
};