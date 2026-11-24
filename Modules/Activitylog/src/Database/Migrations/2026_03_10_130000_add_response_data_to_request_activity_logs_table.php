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
        Schema::table('request_activity_logs', function (Blueprint $table) {
            if (!Schema::hasColumn('request_activity_logs', 'response_headers')) {
                $table->json('response_headers')->nullable()->after('response_status');
            }

            if (!Schema::hasColumn('request_activity_logs', 'response_body')) {
                $table->longText('response_body')->nullable()->after('response_headers');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('request_activity_logs', function (Blueprint $table) {
            $columns = [];

            if (Schema::hasColumn('request_activity_logs', 'response_body')) {
                $columns[] = 'response_body';
            }

            if (Schema::hasColumn('request_activity_logs', 'response_headers')) {
                $columns[] = 'response_headers';
            }

            if (!empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};
