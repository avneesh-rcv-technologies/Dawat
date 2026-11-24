<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $schema = Schema::connection(config('activitylog.database_connection'));
        $tableName = config('activitylog.table_name', 'activity_log');

        if ($schema->hasTable($tableName)) {
            return;
        }

        $schema->create($tableName, function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('log_name')->nullable();
            $table->text('description');
            $table->nullableMorphs('subject', 'subject');
            $table->nullableMorphs('causer', 'causer');
            $table->json('properties')->nullable();
            $table->timestamps();
            $table->index('log_name');
        });
    }

    public function down(): void
    {
        // The earlier module migration owns this schema when both copies ran.
        // A rollback of this compatibility copy must not remove the module's data.
        $migrationsTable = config('database.migrations.table', 'migrations');
        if (Schema::hasTable($migrationsTable)
            && DB::table($migrationsTable)->where('migration', '2025_09_12_060943_create_activity_log_table')->exists()) {
            return;
        }

        $schema = Schema::connection(config('activitylog.database_connection'));
        $tableName = config('activitylog.table_name', 'activity_log');

        $schema->dropIfExists($tableName);
    }
};
