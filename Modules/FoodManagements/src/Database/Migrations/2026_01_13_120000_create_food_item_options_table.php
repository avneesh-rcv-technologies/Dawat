<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('food_item_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('food_item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('food_option_id')->constrained()->cascadeOnDelete();
            $table->decimal('price', 10, 2)->default(0);
            $table->timestamps();

            // Ensure unique combination of food_item_id and food_option_id
            $table->unique(['food_item_id', 'food_option_id'], 'unique_food_item_option');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('food_item_options');
    }
};