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
        Schema::create('shippings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->string('shipping_method_code');
            $table->string('tracking_number')->unique();
            $table->string('status')->default('pending');
            $table->decimal('cost', 10, 2)->default(0);
            $table->string('currency', 3)->default('USD');
            $table->json('delivery_address')->nullable();
            $table->text('delivery_instructions')->nullable();
            $table->timestamp('estimated_delivery_at')->nullable();
            $table->timestamp('actual_delivery_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->foreign('shipping_method_code')->references('code')->on('shipping_methods');
            $table->index(['order_id']);
            $table->index(['tracking_number']);
            $table->index(['status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shippings');
    }
};