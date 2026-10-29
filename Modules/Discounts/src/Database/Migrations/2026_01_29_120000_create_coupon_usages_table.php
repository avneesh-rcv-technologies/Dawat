<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('coupon_usages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coupon_id')->constrained('coupons')->onDelete('cascade');
            
            // Polymorphic relationship to track which entity (User, Admin, etc.) used the coupon
            $table->string('usable_type');
            $table->unsignedBigInteger('usable_id');
            
            // Optional order reference
            $table->unsignedBigInteger('order_id')->nullable();
            
            // Amount deducted by this coupon usage
            $table->decimal('discount_amount', 10, 2)->nullable();
            
            // When the coupon was used
            $table->dateTime('used_at');
            
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['usable_type', 'usable_id']);
            $table->index('coupon_id');
            $table->index('order_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coupon_usages');
    }
};
