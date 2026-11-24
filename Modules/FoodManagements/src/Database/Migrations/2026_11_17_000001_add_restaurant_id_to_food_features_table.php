<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Food features become restaurant-owned: each restaurant manages its own feature groups
 * and features, and they are never shown to other restaurants.
 *
 * The previous platform-wide features have no owner, so they are removed. Descriptions,
 * variants, category links and item values cascade with them.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('food_features')->delete();

        Schema::table('food_features', function (Blueprint $table) {
            $table->foreignId('restaurant_id')->nullable()->after('feature_id')
                ->constrained('restaurants')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('food_features', function (Blueprint $table) {
            $table->dropConstrainedForeignId('restaurant_id');
        });
    }
};
