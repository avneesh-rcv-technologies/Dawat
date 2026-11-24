<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $schema = Schema::connection(config('activitylog.database_connection'));
        $tableName = config('activitylog.table_name', 'activity_log');

        if ($schema->hasColumn($tableName, 'event')) {
            return;
        }

        $schema->table($tableName, function (Blueprint $table) {
            $table->string('event')->nullable()->after('subject_type');
        });
    }

    public function down(): void
    {
        $schema = Schema::connection(config('activitylog.database_connection'));
        $tableName = config('activitylog.table_name', 'activity_log');

        if ($schema->hasTable($tableName) && $schema->hasColumn($tableName, 'event')) {
            $schema->table($tableName, function (Blueprint $table) {
                $table->dropColumn('event');
            });
        }
    }
};
