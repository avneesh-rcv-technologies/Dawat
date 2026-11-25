<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('ratings', 'owner_reported_by')) {
            Schema::table('ratings', function (Blueprint $table) {
                $table->dropConstrainedForeignId('owner_reported_by');
            });
        }

        $columns = array_values(array_filter(
            ['owner_report_reason', 'owner_reported_at'],
            fn (string $column) => Schema::hasColumn('ratings', $column)
        ));

        if ($columns) {
            Schema::table('ratings', fn (Blueprint $table) => $table->dropColumn($columns));
        }
    }

    public function down(): void
    {
        Schema::table('ratings', function (Blueprint $table) {
            $table->text('owner_report_reason')->nullable();
            $table->timestamp('owner_reported_at')->nullable();
            $table->foreignId('owner_reported_by')->nullable()->constrained('admins')->nullOnDelete();
        });
    }
};
