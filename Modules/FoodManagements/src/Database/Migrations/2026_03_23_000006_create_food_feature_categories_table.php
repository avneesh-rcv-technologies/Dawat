<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('food_feature_categories', function (Blueprint $table) {
            $table->unsignedBigInteger('feature_id');
            $table->unsignedBigInteger('category_id');

            $table->primary(['feature_id', 'category_id']);
            $table->index('category_id');

            $table->foreign('feature_id')
                ->references('feature_id')
                ->on('food_features')
                ->cascadeOnDelete();

            $table->foreign('category_id')
                ->references('id')
                ->on('food_categories')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('food_feature_categories');
    }
};
