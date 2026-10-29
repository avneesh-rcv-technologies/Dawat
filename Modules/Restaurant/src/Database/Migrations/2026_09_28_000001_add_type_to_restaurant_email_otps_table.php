<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('restaurant_email_otps', function (Blueprint $table) {
            if (!Schema::hasColumn('restaurant_email_otps', 'type')) {
                $table->string('type')->nullable()->default('email_verification')->after('email');
            }
        });
    }

    public function down(): void
    {
        Schema::table('restaurant_email_otps', function (Blueprint $table) {
            if (Schema::hasColumn('restaurant_email_otps', 'type')) {
                $table->dropColumn('type');
            }
        });
    }
};
