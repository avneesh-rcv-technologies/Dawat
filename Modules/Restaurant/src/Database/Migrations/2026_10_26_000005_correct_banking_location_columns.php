<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('restaurant_banking_details', function (Blueprint $table) {
            if (!Schema::hasColumn('restaurant_banking_details', 'country_id')) {
                $table->unsignedBigInteger('country_id')->nullable();
            }

            if (!Schema::hasColumn('restaurant_banking_details', 'state_id')) {
                $table->unsignedBigInteger('state_id')->nullable();
            }

            if (!Schema::hasColumn('restaurant_banking_details', 'city_id')) {
                $table->unsignedBigInteger('city_id')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('restaurant_banking_details', function (Blueprint $table) {
            $table->dropColumn(['country_id', 'state_id', 'city_id']);
        });
    }
};