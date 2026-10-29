<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('food_items', function (Blueprint $table) {
            $table->id();

            // Basic info
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->longText('long_description')->nullable();

            // Pricing
            $table->decimal('sale_price', 10, 2);
            $table->decimal('cost_price', 10, 2)->nullable();
            $table->decimal('list_price', 10, 2)->nullable();

            // Quantity (optional / combos)
            $table->integer('quantity')->nullable();

            // Availability
            $table->boolean('is_available')->default(true);
            $table->boolean('is_active')->default(false);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('food_items');
    }
};
