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
        if (! Schema::hasColumn('dietary_labels', 'description')) {
            Schema::table('dietary_labels', function (Blueprint $table) {
                $table->text('description')->nullable()->after('icon');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('dietary_labels', 'description')) {
            Schema::table('dietary_labels', function (Blueprint $table) {
                $table->dropColumn('description');
            });
        }
    }
};
