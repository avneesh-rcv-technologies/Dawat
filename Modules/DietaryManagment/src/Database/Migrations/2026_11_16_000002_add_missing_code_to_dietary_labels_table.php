<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        // Older installations predate the code column in the create-table migration.
        if (! Schema::hasColumn('dietary_labels', 'code')) {
            Schema::table('dietary_labels', function (Blueprint $table) {
                $table->string('code', 10)->nullable();
            });
        }

        $codes = [
            'HALAL' => 'H',
            'KOSHER' => 'K',
            'VEGETARIAN' => 'V',
            'VEGAN' => 'VG',
            'GLUTEN_FREE' => 'GF',
            'DAIRY_FREE' => 'DF',
        ];

        DB::table('dietary_labels')
            ->where(function ($query) {
                $query->whereNull('code')->orWhere('code', '');
            })
            ->orderBy('id')
            ->chunkById(100, function ($labels) use ($codes) {
                foreach ($labels as $label) {
                    DB::table('dietary_labels')->where('id', $label->id)->update([
                        'code' => $codes[$label->backend_value]
                            ?? Str::substr(Str::upper($label->backend_value), 0, 10),
                    ]);
                }
            });
    }

    public function down(): void
    {
        // Preserve codes: this column may already have existed before this repair.
    }
};
