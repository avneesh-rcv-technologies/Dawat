<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('food_categories', function (Blueprint $table) {
            $table->unsignedBigInteger('restaurant_id')
                ->nullable()
                ->after('id');

            $table->foreign('restaurant_id')
                ->references('id')
                ->on('restaurants')
                ->nullOnDelete();

            $table->index('restaurant_id');
        });
    }

    public function down(): void
    {
        Schema::table('food_categories', function (Blueprint $table) {
            $table->dropForeign(['restaurant_id']);
            $table->dropIndex(['restaurant_id']);
            $table->dropColumn('restaurant_id');
        });
    }
};
