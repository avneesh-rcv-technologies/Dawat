<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            // Drop the direct plan_id foreign key
            $table->dropForeign(['plan_id']);
            
            // Make plan_id nullable (kept for denormalization/backward compatibility)
            $table->unsignedBigInteger('plan_id')->nullable()->change();
            
            // Add subscription_product_id as NULLABLE first (will populate then make NOT NULL)
            $table->unsignedBigInteger('subscription_product_id')->nullable()->after('plan_id')
                  ->comment('Required - links to subscription_products (product or direct plan purchase)');
            
            $table->foreign('subscription_product_id')
                  ->references('id')
                  ->on('subscription_products')
                  ->onDelete('restrict');
                  
            $table->index('subscription_product_id');
        });

        // Now make subscription_product_id NOT NULL
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->unsignedBigInteger('subscription_product_id')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropIndex(['subscription_product_id']);
            $table->dropForeign(['subscription_product_id']);
            $table->dropColumn('subscription_product_id');
            
            // Restore direct plan_id constraint
            $table->foreign('plan_id')->references('id')->on('plans')->onDelete('restrict');
        });
    }
};
