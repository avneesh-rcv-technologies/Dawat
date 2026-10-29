<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\GeneralSetting\Models\Setting;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add the tax application level setting
        if (function_exists('fn_set_setting')) {
            fn_set_setting('tax.application_level', 'food_item');
        } else {
            // Fallback if settings helper is not available
            Setting::updateOrCreate(
                ['key' => 'tax.application_level'],
                ['value' => 'food_item']
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove the tax application level setting
        if (function_exists('fn_delete_setting')) {
            fn_delete_setting('tax.application_level');
        } else {
            // Fallback if settings helper is not available
            Setting::where('key', 'tax.application_level')->delete();
        }
    }
};