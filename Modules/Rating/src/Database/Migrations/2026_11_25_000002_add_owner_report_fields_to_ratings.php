<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ratings', function (Blueprint $table) {
            $table->text('owner_report_reason')->nullable();
            $table->timestamp('owner_reported_at')->nullable();
            $table->foreignId('owner_reported_by')->nullable()->constrained('admins')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('ratings', function (Blueprint $table) {
            $table->dropConstrainedForeignId('owner_reported_by');
            $table->dropColumn(['owner_report_reason', 'owner_reported_at']);
        });
    }
};
