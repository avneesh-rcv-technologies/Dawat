<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('allowed_ips', function (Blueprint $table) {
            if (!Schema::hasColumn('allowed_ips', 'expires_at')) {
                $table->timestamp('expires_at')->nullable()->index()->after('is_active');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('allowed_ips', function (Blueprint $table) {
            if (Schema::hasColumn('allowed_ips', 'expires_at')) {
                $table->dropColumn('expires_at');
            }
        });
    }
};
