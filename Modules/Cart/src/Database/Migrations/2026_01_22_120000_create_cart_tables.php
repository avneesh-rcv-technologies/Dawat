<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Cart table - stores cart sessions with polymorphic relations
        Schema::create('carts', function (Blueprint $table) {
            $table->id();
            $table->string('session_id')->nullable(); // For guest users
            $table->nullableMorphs('owner'); // Polymorphic relation for user/guest ownership (nullable for guests)
            $table->morphs('cartable'); // Polymorphic relation for restaurant/vendor
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->decimal('total', 10, 2)->default(0);
            $table->boolean('is_tax_inclusive')->default(false);
            $table->json('tax_breakdown')->nullable(); // Store detailed tax information
            $table->string('coupon_code')->nullable();
            $table->nullableMorphs('discount'); // Polymorphic relation for discount (nullable)
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('discount_percentage', 10, 2)->nullable();
            $table->json('discount_breakdown')->nullable();
            $table->timestamps();

            // Indexes for better performance
            $table->index(['session_id', 'cartable_type', 'cartable_id']);
            $table->index(['owner_type', 'owner_id', 'cartable_type', 'cartable_id']);
        });

        // Cart items table - stores individual items with polymorphic relations and clean naming
        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cart_id')->constrained()->onDelete('cascade');
            $table->morphs('item'); // Polymorphic relation for food_item/product (clean naming: item_id instead of food_item_id)
            $table->integer('quantity');
            $table->decimal('unit_price', 10, 2); // Price at time of adding to cart
            $table->decimal('unit_display_price', 10, 2); // Display price with tax
            $table->decimal('unit_tax_amount', 10, 2)->default(0);
            $table->decimal('total_price', 10, 2); // unit_price * quantity
            $table->decimal('total_display_price', 10, 2); // unit_display_price * quantity
            $table->decimal('total_tax_amount', 10, 2)->default(0);
            $table->boolean('is_tax_inclusive')->default(false);
            $table->json('tax_breakdown')->nullable(); // Tax details for this item
            $table->json('selected_options')->nullable(); // Store selected options with prices
            $table->text('special_instructions')->nullable();
            $table->timestamps();

            // Indexes for better performance
            $table->index(['cart_id', 'item_type', 'item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cart_items');
        Schema::dropIfExists('carts');
    }
};