<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Fresh installations already receive the corrected columns.
        if (!Schema::hasColumn('tax_information', 'city')) {
            return;
        }
        if (DB::table('tax_information')->exists()) {
            throw new RuntimeException('Existing tax addresses must be mapped to location IDs before changing the columns.');
        }
        Schema::table('tax_information', function (Blueprint $table) {
            $table->dropColumn(['city', 'state', 'country']);
            $table->integer('city_id');
            $table->integer('state_id');
            $table->integer('country_id');
        });
    }

    public function down(): void
    {
        // Retain the corrected schema; the create migration owns this table's rollback.
    }
};
