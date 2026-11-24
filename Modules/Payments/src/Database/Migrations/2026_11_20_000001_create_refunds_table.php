<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('refunds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->restrictOnDelete();
            $table->foreignId('payment_id')->constrained('payments')->restrictOnDelete();
            // Each request owns one refund transaction throughout its lifecycle.
            $table->foreignId('transaction_id')->nullable()->unique()->constrained('transactions')->restrictOnDelete();
            $table->decimal('refund_amount', 10, 2)->nullable();
            $table->text('refund_reason')->nullable();
            $table->enum('refund_source', ['customer_cancellation', 'restaurant_cancellation', 'admin']);
            $table->enum('refund_status', ['pending', 'processing', 'succeeded', 'failed', 'rejected'])->default('pending');
            $table->string('stripe_refund_id')->nullable()->unique();
            $table->text('admin_notes')->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('admins')->nullOnDelete();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
            $table->unique(['order_id', 'payment_id']);
            $table->index(['order_id', 'refund_status']);
        });

        Schema::create('refund_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('refund_id')->constrained('refunds')->restrictOnDelete();
            $table->foreignId('changed_by')->nullable()->constrained('admins')->nullOnDelete();
            $table->string('old_status')->nullable();
            $table->string('new_status');
            $table->json('before')->nullable();
            $table->json('after');
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('refund_status_histories');
        Schema::dropIfExists('refunds');
    }
};
