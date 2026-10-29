<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kyc_types', function (Blueprint $table) {
            $table->enum('type', ['kyc', 'compliance'])->default('kyc')->after('name');
        });
    }

    public function down(): void
    {
        Schema::table('kyc_types', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};
