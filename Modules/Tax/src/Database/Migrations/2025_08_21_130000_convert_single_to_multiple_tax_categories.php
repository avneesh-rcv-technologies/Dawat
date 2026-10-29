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
        // Convert single tax category to multiple categories array
        $oldCategoryId = null;
        
        if (function_exists('fn_get_setting')) {
            $oldCategoryId = fn_get_setting('tax.default_category_id');
        } else {
            // Fallback if settings helper is not available
            $setting = Setting::where('key', 'tax.default_category_id')
                ->value('value');
            $oldCategoryId = $setting ? $setting->value : null;
        }
        
        // Convert to array format
        $newCategoryIds = [];
        if ($oldCategoryId && is_numeric($oldCategoryId)) {
            $newCategoryIds = [$oldCategoryId];
        }
        
        // Set the new multiple categories setting
        if (function_exists('fn_set_setting')) {
            fn_set_setting('tax.default_category_ids', json_encode($newCategoryIds));
        } else {
            // Fallback if settings helper is not available
             Setting::updateOrCreate(
                ['key' => 'tax.default_category_ids'],
                ['value' => json_encode($newCategoryIds)]
            );
        }
        
        // Remove the old single category setting
        if (function_exists('fn_delete_setting')) {
            fn_delete_setting('tax.default_category_id');
        } else {
            // Fallback if settings helper is not available
             Setting::where('key', 'tax.default_category_id')->delete();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Convert back to single category
        $categoryIds = [];
        
        if (function_exists('fn_get_setting')) {
            $categoryIds = fn_get_setting('tax.default_category_ids', []);
        } else {
            // Fallback if settings helper is not available
            $setting = Setting::where('key', 'tax.default_category_ids')
                ->value('value');
            $categoryIds = $setting ? json_decode($setting->value, true) : [];
        }
        
        if (is_string($categoryIds)) {
            $categoryIds = json_decode($categoryIds, true) ?: [];
        }
        
        // Take the first category ID as the single default
        $singleCategoryId = !empty($categoryIds) ? $categoryIds[0] : null;
        
        // Set the old single category setting
        if (function_exists('fn_set_setting')) {
            fn_set_setting('tax.default_category_id', $singleCategoryId);
        } else {
            // Fallback if settings helper is not available
             Setting::updateOrCreate(
                ['key' => 'tax.default_category_id'],
                ['value' => $singleCategoryId]
            );
        }
        
        // Remove the multiple categories setting
        if (function_exists('fn_delete_setting')) {
            fn_delete_setting('tax.default_category_ids');
        } else {
            // Fallback if settings helper is not available
            Setting::where('key', 'tax.default_category_ids')->delete();
        }
    }
};