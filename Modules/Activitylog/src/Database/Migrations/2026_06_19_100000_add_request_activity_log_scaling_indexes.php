<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        DB::statement(
            'CREATE INDEX ral_dedup_lookup_idx ON request_activity_logs '
            . '(ip_address, guard, causer_id, method, path(191), response_status, is_blocked, created_at)'
        );

        Schema::create('request_activity_log_rollups', function (Blueprint $table) {
            $table->id();
            $table->date('bucket_date');
            $table->unsignedTinyInteger('bucket_hour')->nullable();
            $table->unsignedBigInteger('total_requests')->default(0);
            $table->unsignedBigInteger('blocked_requests')->default(0);
            $table->unsignedBigInteger('error_requests')->default(0);
            $table->unsignedBigInteger('unique_ips')->default(0);
            $table->unsignedBigInteger('total_duration_ms')->default(0);
            $table->timestamps();

            $table->unique(['bucket_date', 'bucket_hour'], 'ral_rollups_bucket_unique');
            $table->index('bucket_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('request_activity_log_rollups');

        Schema::table('request_activity_logs', function (Blueprint $table) {
            $table->dropIndex('ral_dedup_lookup_idx');
        });
    }
};
