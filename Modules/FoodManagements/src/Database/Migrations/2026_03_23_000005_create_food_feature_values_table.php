<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('food_feature_values', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('feature_id');
            $table->unsignedBigInteger('food_item_id');

            // NULL = custom text value, >0 = feature variant
            $table->unsignedBigInteger('variant_id')->nullable();

            $table->string('value', 255)->default('');
            $table->decimal('value_int', 12, 2)->nullable();

            $table->char('lang_code', 2)->default('en');

            $table->unique(
                ['feature_id', 'food_item_id', 'variant_id', 'lang_code'],
                'uq_food_feature_values_feature_item_variant_lang'
            );

            $table->index(['feature_id', 'lang_code']);
            $table->index('food_item_id');
            $table->index('variant_id');

            $table->foreign('feature_id')
                ->references('feature_id')
                ->on('food_features')
                ->cascadeOnDelete();

            $table->foreign('food_item_id')
                ->references('id')
                ->on('food_items')
                ->cascadeOnDelete();

            $table->foreign('variant_id')
                ->references('variant_id')
                ->on('food_feature_variants')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('food_feature_values');
    }
};
