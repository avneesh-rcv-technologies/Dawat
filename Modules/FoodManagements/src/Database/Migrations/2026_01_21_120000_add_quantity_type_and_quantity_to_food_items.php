<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('food_items', function (Blueprint $table) {
            // Add quantity type dropdown field (manual or grouping)
            $table->enum('quantity_type', ['manual', 'grouping'])->default('manual')->after('in_stock');
            
            // Add quantity field that works differently based on type
            // For manual: represents actual quantity/stock
            // For grouping: represents minimum guest count
            $table->integer('quantity')->nullable()->after('quantity_type');
        });
    }

    public function down(): void
    {
        Schema::table('food_items', function (Blueprint $table) {
            $table->dropColumn(['quantity_type', 'quantity']);
        });
    }
};