<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('food_item_serving_options', function (Blueprint $table) {
            $table->string('serve_type', 20)->default('tray');
            $table->unsignedInteger('min_guests')->nullable();
            $table->unsignedInteger('max_guests')->nullable();
            $table->decimal('quantity', 10, 2)->nullable()->change();
            $table->string('unit', 30)->nullable()->change();
            $table->unsignedInteger('serves')->nullable()->change();
        });
    }

    public function down(): void
    {
        DB::table('food_item_serving_options')->whereNull('quantity')->update(['quantity' => 1]);
        DB::table('food_item_serving_options')->whereNull('unit')->update(['unit' => 'Pieces']);
        DB::table('food_item_serving_options')->whereNull('serves')->update(['serves' => 1]);

        Schema::table('food_item_serving_options', function (Blueprint $table) {
            $table->dropColumn(['serve_type', 'min_guests', 'max_guests']);
            $table->decimal('quantity', 10, 2)->nullable(false)->change();
            $table->string('unit', 30)->nullable(false)->change();
            $table->unsignedInteger('serves')->nullable(false)->change();
        });
    }
};
