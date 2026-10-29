<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('food_items', function (Blueprint $table) {
            // Remove quantity field
            $table->dropColumn('quantity');
            
            // Add in_stock boolean field
            $table->boolean('in_stock')->default(true)->after('list_price');
        });
    }

    public function down(): void
    {
        Schema::table('food_items', function (Blueprint $table) {
            // Add back quantity field
            $table->integer('quantity')->nullable()->after('list_price');
            
            // Remove in_stock field
            $table->dropColumn('in_stock');
        });
    }
};