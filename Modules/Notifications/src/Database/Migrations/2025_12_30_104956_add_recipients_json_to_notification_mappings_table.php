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
        Schema::table('notification_mappings', function (Blueprint $table) {
            $table->json('recipients')->nullable()->comment('Dynamic recipients storage');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notification_mappings', function (Blueprint $table) {
            $table->dropColumn('recipients');
        });
    }
};
