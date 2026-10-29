<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('restaurant_email_otps', function (Blueprint $table) {
            $table->text('otp')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('restaurant_email_otps', function (Blueprint $table) {
            $table->string('otp', 10)->nullable()->change();
        });
    }
};