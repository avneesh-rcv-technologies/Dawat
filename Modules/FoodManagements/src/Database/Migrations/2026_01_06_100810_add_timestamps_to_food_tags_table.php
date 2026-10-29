<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
       Schema::table('food_tags', function (Blueprint $table) {
            if (!Schema::hasColumn('food_tags', 'created_at')) {
                $table->timestamp('created_at')->nullable();
            }

            if (!Schema::hasColumn('food_tags', 'updated_at')) {
                $table->timestamp('updated_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('food_tags', function (Blueprint $table) {
            if (Schema::hasColumn('food_tags', 'created_at')) {
                $table->dropColumn('created_at');
            }

            if (Schema::hasColumn('food_tags', 'updated_at')) {
                $table->dropColumn('updated_at');
            }
        });
    }
};
