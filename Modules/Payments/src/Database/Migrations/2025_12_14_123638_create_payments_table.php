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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            
            // Polymorphic vendor relationship (can be Restaurant, Store, etc.)
            $table->morphs('vendor');
            
            $table->foreignId('payment_method_id')->constrained('payment_methods')->onDelete('restrict');
            $table->string('transaction_id')->nullable()->comment('Gateway transaction ID');
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('USD');
            $table->enum('status', ['pending', 'processing', 'completed', 'failed', 'refunded', 'partially_refunded'])->default('pending')->index();
            $table->enum('payment_type', ['order_payment', 'refund', 'partial_refund'])->default('order_payment');
            $table->json('details')->nullable()->comment('Gateway response and metadata');
            $table->string('gateway_reference')->nullable()->comment('Payment gateway reference');
            $table->decimal('gateway_fee', 8, 2)->default(0)->comment('Payment gateway processing fee');
            $table->string('failure_reason')->nullable();
            $table->datetime('processed_at')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index('transaction_id');
            $table->index(['order_id', 'status']);
            $table->index('gateway_reference');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};