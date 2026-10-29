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
        Schema::table('customers', function (Blueprint $table) {
            $table->enum('guest_method', ['Delivery', 'Pickup'])->default('Delivery')->after('role_id');
            $table->integer('number_of_guests')->nullable()->comment('Number of guests')->after('guest_method');
            $table->dateTime('preferred_delivery_date')->nullable()->after('number_of_guests');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn(['guest_method', 'number_of_guests', 'preferred_delivery_date']);
        });
    }
};
