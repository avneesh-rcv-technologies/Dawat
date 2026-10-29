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
        Schema::create('subscription_products', function (Blueprint $table) {
            $table->id();
            // Product morphs are nullable - null means direct plan purchase (no specific product)
            $table->string('product_type')->nullable()->comment('Type of product (null for direct plan purchases)');
            $table->unsignedBigInteger('product_id')->nullable()->comment('ID of product (null for direct plan purchases)');
            $table->unsignedBigInteger('plan_id')->comment('Subscription plan');
            $table->decimal('price', 10, 2)->comment('Price for this product-plan combination');
            $table->boolean('is_active')->default(true)->comment('Whether this mapping is active');
            // Type: 'product' for admin-mapped products, 'direct' for direct plan purchases
            $table->enum('type', ['product', 'direct'])->default('product')->comment('Entry type: product mapping or direct purchase');
            $table->timestamps();

            $table->foreign('plan_id')->references('id')->on('plans')->onDelete('cascade');
            // Index for nullable morphs
            $table->index(['product_type', 'product_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_products');
    }
};
