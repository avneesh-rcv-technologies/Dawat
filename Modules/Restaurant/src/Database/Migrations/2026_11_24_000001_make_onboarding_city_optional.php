<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('tax_information', 'city_id')) {
            Schema::table('tax_information', function (Blueprint $table) {
                $table->integer('city_id')->nullable()->change();
            });
        }
        if (Schema::hasColumn('restaurant_banking_details', 'city_id')) {
            Schema::table('restaurant_banking_details', function (Blueprint $table) {
                $table->unsignedBigInteger('city_id')->nullable()->change();
            });
        }
    }

    public function down(): void
    {
        // Keep nullable columns: records created without a city must remain valid.
    }
};
