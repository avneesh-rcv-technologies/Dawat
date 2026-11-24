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
        if (!Schema::hasColumn('dietary_labels', 'slug')) {
            Schema::table('dietary_labels', function (Blueprint $table) {
                $table->string('slug', 100)->nullable()->unique()->after('code');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('dietary_labels', 'slug')) {
            Schema::table('dietary_labels', function (Blueprint $table) {
                $table->dropUnique(['slug']);
                $table->dropColumn('slug');
            });
        }
    }
};
