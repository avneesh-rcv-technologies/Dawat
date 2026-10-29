<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Modules\FoodManagements\Models\FoodCategory;

return new class extends Migration {
    public function up(): void
    {
        // Check if slug column already exists
        if (!Schema::hasColumn('food_categories', 'slug')) {
            // Add the slug column without unique constraint
            Schema::table('food_categories', function (Blueprint $table) {
                $table->string('slug')->nullable()->after('name');
            });
        }

        // Generate slugs for existing records
        $categories = FoodCategory::whereNull('slug')->orWhere('slug', '')->get();
        foreach ($categories as $category) {
            $slug = Str::slug($category->name);
            $original = $slug;
            $count = 1;

            // Ensure uniqueness
            while (FoodCategory::where('slug', $slug)->where('id', '!=', $category->id)->exists()) {
                $slug = $original . '-' . $count++;
            }

            $category->update(['slug' => $slug]);
        }

        // Check if unique constraint exists and add if not
        try {
            Schema::table('food_categories', function (Blueprint $table) {
                $table->unique('slug');
            });
        } catch (\Exception $e) {
            // Unique constraint might already exist, ignore error
        }
    }

    public function down(): void
    {
        Schema::table('food_categories', function (Blueprint $table) {
            $table->dropColumn('slug');
        });
    }
};