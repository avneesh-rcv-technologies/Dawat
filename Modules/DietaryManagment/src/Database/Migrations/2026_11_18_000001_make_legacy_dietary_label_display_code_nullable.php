<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Current schemas use code and do not have this legacy column.
        if (! Schema::hasTable('dietary_labels') || ! Schema::hasColumn('dietary_labels', 'display_code')) {
            return;
        }

        Schema::table('dietary_labels', function (Blueprint $table) {
            $table->string('display_code', 255)->nullable()->default(null)->change();
        });
    }

    public function down(): void
    {
        // Keep the compatibility repair: new records may now contain NULL, and
        // the original column definition differs between older installations.
    }
};
