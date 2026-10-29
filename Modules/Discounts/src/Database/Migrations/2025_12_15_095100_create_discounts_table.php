<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('discounts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('admin_id')->constrained();
            $table->text('description')->nullable();
            $table->string('type'); // fixed, percentage, free_shipping
            $table->decimal('amount', 10, 2);
            $table->string('apply_to')->default('subtotal'); // subtotal, total, shipping
            $table->json('user_groups')->nullable();
            $table->boolean('is_active')->default(true);
            $table->dateTime('starts_at')->nullable();
            $table->dateTime('expires_at')->nullable();
            $table->timestamps();
        });

        // coupons table
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('discount_id')->constrained();
            $table->string('code', 40)->unique();
            $table->text('description')->nullable();
            $table->dateTime('starts_at')->nullable();
            $table->dateTime('expires_at')->nullable();
            $table->integer('usage_limit')->nullable();
            $table->integer('usage_limit_per_user')->nullable();
            $table->decimal('min_order_amount', 10, 2)->nullable();
            $table->integer('times_used')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // discount_rules table
        Schema::create('discount_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('discount_id')->constrained();
            $table->string('rule_type');
            $table->unsignedBigInteger('rule_id')->nullable();
            $table->string('rule_value')->nullable();
            $table->timestamps();
        });

        // indexes for performance
        Schema::table('discount_rules', function (Blueprint $table) {
            $table->index('discount_id');
            $table->index(['rule_type', 'rule_id']);
        });

        Schema::table('coupons', function (Blueprint $table) {
            $table->index('discount_id');
            $table->index('code');
        });

        Schema::table('discounts', function (Blueprint $table) {
            $table->index('is_active');
            $table->index('starts_at');
            $table->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('discount_rules');
        Schema::dropIfExists('coupons');
        Schema::dropIfExists('discounts');
    }
};
