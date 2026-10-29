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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            
            // Order relationship
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            
            // Payment reference (if applicable)
            $table->foreignId('payment_id')->nullable()->constrained('payments')->onDelete('set null');
            
            // Transaction details
            $table->string('transaction_number', 109)->unique();
            $table->string('type', 100);
            $table->decimal('amount', 10, 2)->nullable();
            $table->decimal('balance', 10, 2)->nullable()->comment('Running balance after this transaction');
            $table->string('currency', 3);
            
            // Status tracking
            $table->string('status', 60);
            
            // Financial tracking
            $table->dateTime('processed_at')->nullable();
            $table->foreignId('processed_by')->nullable();
            
            // Additional information
            $table->string('gateway_reference')->nullable()->comment('Gateway transaction ID');
            $table->json('metadata')->nullable();
            $table->text('notes')->nullable();
            
            // Timestamps
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('transaction_number');
            $table->index('type');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};