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
        Schema::table('tax_rules', function (Blueprint $table) {
            // Drop existing foreign key constraints
            $table->dropForeign(['tax_category_id']);
            $table->dropForeign(['tax_rate_id']);
            
            // Re-add foreign key constraints with cascade on delete
            $table->foreign('tax_category_id')
                  ->references('id')
                  ->on('tax_categories')
                  ->onDelete('cascade');
                  
            $table->foreign('tax_rate_id')
                  ->references('id')
                  ->on('tax_rates')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tax_rules', function (Blueprint $table) {
            // Drop the foreign key constraints
            $table->dropForeign(['tax_category_id']);
            $table->dropForeign(['tax_rate_id']);
            
            // Re-add the original foreign key constraints without cascade
            $table->foreign('tax_category_id')
                  ->references('id')
                  ->on('tax_categories');
                  
            $table->foreign('tax_rate_id')
                  ->references('id')
                  ->on('tax_rates');
        });
    }
};
