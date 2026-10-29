<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tax_information', function (Blueprint $table) {
            $table->dropUnique(['taxable_id', 'taxable_type']);
            $table->unique(['taxable_id', 'taxable_type', 'tax_type'], 'tax_information_owner_type_unique');
        });
    }

    public function down(): void
    {
        if (DB::table('tax_information')->select('taxable_id', 'taxable_type')
            ->groupBy('taxable_id', 'taxable_type')->havingRaw('COUNT(*) > 1')->exists()) {
            throw new RuntimeException('Cannot restore one-record-per-owner constraint while multiple tax types exist.');
        }
        Schema::table('tax_information', function (Blueprint $table) {
            $table->dropUnique('tax_information_owner_type_unique');
            $table->unique(['taxable_id', 'taxable_type']);
        });
    }
};
