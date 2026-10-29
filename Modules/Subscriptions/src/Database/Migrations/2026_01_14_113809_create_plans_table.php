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
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name', 128)->comment('Plan name e.g., Basic, Premium, Enterprise');
            $table->text('description')->nullable()->comment('Details about the plan');
            $table->decimal('price', 10, 2)->default(0)->comment('Cost per billing cycle');
            $table->enum('billing_cycle', ['daily', 'weekly', 'monthly', 'quarterly', 'yearly', 'one-time'])
                  ->default('monthly')
                  ->comment('Billing frequency');
            $table->json('features')->nullable()->comment('Plan features in JSON format');
            $table->integer('trial_days')->default(0)->comment('Number of trial days');
            $table->integer('max_users')->nullable()->comment('Maximum users allowed (null = unlimited)');
            $table->integer('max_projects')->nullable()->comment('Maximum projects allowed (null = unlimited)');
            $table->boolean('is_active')->default(true)->comment('Whether the plan is currently offered');
            $table->boolean('is_popular')->default(false)->comment('Highlight as popular plan');
            $table->integer('sort_order')->default(0)->comment('Display order');
            $table->softDeletes();
            $table->timestamps();

            // Indexes
            $table->index('is_active');
            $table->index('billing_cycle');
            $table->index('sort_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
