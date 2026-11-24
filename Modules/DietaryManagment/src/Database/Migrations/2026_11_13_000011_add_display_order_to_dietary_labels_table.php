<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('dietary_labels', 'display_order')) {
            Schema::table('dietary_labels', function (Blueprint $table) {
                $table->integer('display_order')->default(0);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('dietary_labels', 'display_order')) {
            Schema::table('dietary_labels', function (Blueprint $table) {
                $table->dropColumn('display_order');
            });
        }
    }
};
