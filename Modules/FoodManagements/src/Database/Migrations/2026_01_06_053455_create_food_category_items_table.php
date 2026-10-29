<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('food_category_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('food_item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('food_category_id')->constrained('food_categories')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('food_category_items');
    }
};
