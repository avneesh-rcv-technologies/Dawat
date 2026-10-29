<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kyc_fields', function (Blueprint $table) {
            $table->json('validation_rules')->nullable()->after('options');
        });
    }

    public function down(): void
    {
        Schema::table('kyc_fields', function (Blueprint $table) {
            $table->dropColumn('validation_rules');
        });
    }
};
