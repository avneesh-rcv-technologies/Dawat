<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('restaurants', function (Blueprint $table) {
            if (!Schema::hasColumn('restaurants', 'order_time_limit')) {
                $table->integer('order_time_limit')
                    ->default(30)
                    ->after('is_accepting_orders');
            }
        });
    }

    public function down(): void
    {
        Schema::table('restaurants', function (Blueprint $table) {
            if (Schema::hasColumn('restaurants', 'order_time_limit')) {
                $table->dropColumn('order_time_limit');
            }
        });
    }
};