<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_commissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->unique()->constrained('orders')->restrictOnDelete();
            $table->foreignId('restaurant_id')->constrained('restaurants')->restrictOnDelete();
            $table->string('policy_reference')->nullable();
            $table->json('policy_snapshot');
            $table->decimal('order_amount', 12, 2);
            $table->decimal('commission_percentage', 5, 2);
            $table->decimal('platform_commission_amount', 12, 2);
            $table->decimal('restaurant_amount', 12, 2);
            $table->char('currency', 3)->default('USD');
            $table->enum('status', ['pending', 'processed', 'failed'])->default('pending');
            $table->text('admin_notes')->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('admins')->nullOnDelete();
            $table->timestamp('processed_at')->nullable();
            $table->timestamp('calculated_at');
            $table->timestamps();
            $table->index(['restaurant_id', 'status']);
        });
        Schema::create('order_commission_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_commission_id')->constrained('order_commissions')->restrictOnDelete();
            $table->string('old_status')->nullable();
            $table->string('new_status');
            $table->foreignId('changed_by')->nullable()->constrained('admins')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_commission_status_histories');
        Schema::dropIfExists('order_commissions');
    }
};
