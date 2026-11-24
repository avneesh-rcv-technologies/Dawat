<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('food_item_serving_options', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('food_item_id')->index();
            $table->string('serving_name', 100);
            $table->decimal('quantity', 10, 2);
            $table->string('unit', 30);
            $table->unsignedInteger('serves');
            $table->decimal('price', 10, 2);
            $table->decimal('discount_price', 10, 2)->nullable();
            $table->boolean('is_available')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->foreign('food_item_id')
                ->references('id')->on('food_items')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('food_item_serving_options');
    }
};
