<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            
            // Polymorphic relationship to item (food_items, products, etc.)
            $table->morphs('item');
            
            $table->string('item_name');
            $table->string('sku')->nullable();
            $table->decimal('price', 10, 2);
            $table->integer('quantity');
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->json('selected_options')->nullable()->comment('Selected item options with prices');
            $table->text('special_instructions')->nullable();
            $table->decimal('total_price', 10, 2)->comment('price * quantity + options');
            $table->timestamps();
            
            // Indexes
            $table->index(['order_id', 'item_type', 'item_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
