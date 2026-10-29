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
       Schema::create('orders', function (Blueprint $table) {
            $table->id();
            
            // Polymorphic customer relationship (User or Customer)
            $table->morphs('customer');
            
            // Polymorphic vendor relationship (can be Restaurant, Store, etc.)
            $table->morphs('vendor');
            
            $table->foreignId('admin_id')
                ->nullable()
                ->constrained('admins')
                ->nullOnDelete();
            
            $table->string('order_number')->unique();
            $table->char('status', 1)->default('P')->index()->comment('Status code from statuses table');
            $table->enum('order_type', ['delivery', 'takeaway', 'dine_in'])->default('delivery');
            $table->decimal('subtotal', 10, 2);
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('tax', 10, 2)->default(0);
            $table->decimal('delivery_fee', 10, 2)->default(0);
            $table->decimal('service_charge', 10, 2)->default(0);
            $table->decimal('total', 10, 2);
            $table->text('notes')->nullable();
            $table->string('currency', 3)->default('USD');
            $table->string('coupon_code')->nullable();
            $table->string('payment_method')->nullable();
            $table->enum('payment_status', ['pending', 'paid', 'failed', 'refunded'])->default('pending');
            $table->json('delivery_address')->nullable();
            $table->json('billing_address')->nullable();
            $table->datetime('estimated_delivery_time')->nullable();
            $table->datetime('actual_delivery_time')->nullable();
            $table->string('delivery_instructions')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->enum('created_by', ['customer', 'vendor'])->default('customer');
            $table->timestamps();
            
            // Foreign key for status
            $table->foreign('status')
                ->references('status_code')
                ->on('statuses')
                ->onDelete('restrict');
            
            // Indexes
            $table->index('order_number');
            $table->index(['status', 'created_at']);
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
