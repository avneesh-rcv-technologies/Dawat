<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('food_events', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();
        });

        // Pivot table for food events and food items
        Schema::create('food_event_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('food_event_id')->constrained('food_events')->onDelete('cascade');
            $table->foreignId('food_item_id')->constrained('food_items')->onDelete('cascade');
            $table->timestamps();
            
            $table->unique(['food_event_id', 'food_item_id']);
        });

        // Pivot table for food events and food categories
        Schema::create('food_event_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('food_event_id')->constrained('food_events')->onDelete('cascade');
            $table->foreignId('food_category_id')->constrained('food_categories')->onDelete('cascade');
            $table->timestamps();
            
            $table->unique(['food_event_id', 'food_category_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('food_event_categories');
        Schema::dropIfExists('food_event_items');
        Schema::dropIfExists('food_events');
    }
};