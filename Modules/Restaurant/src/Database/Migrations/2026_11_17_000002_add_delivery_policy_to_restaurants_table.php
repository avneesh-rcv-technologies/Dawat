<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('restaurants')) {
            return;
        }

        foreach (['delivery_radius_miles', 'delivery_cost_per_mile', 'free_delivery_enabled', 'free_delivery_threshold'] as $column) {
            if (Schema::hasColumn('restaurants', $column)) {
                continue;
            }
            Schema::table('restaurants', function (Blueprint $table) use ($column) {
                match ($column) {
                    'delivery_radius_miles' => $table->decimal($column, 8, 2)->nullable(),
                    'delivery_cost_per_mile' => $table->decimal($column, 8, 2)->default(0),
                    'free_delivery_enabled' => $table->boolean($column)->default(false),
                    'free_delivery_threshold' => $table->decimal($column, 8, 2)->nullable(),
                };
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('restaurants')) {
            return;
        }
        foreach (['delivery_radius_miles', 'delivery_cost_per_mile', 'free_delivery_enabled', 'free_delivery_threshold'] as $column) {
            if (Schema::hasColumn('restaurants', $column)) {
                Schema::table('restaurants', fn (Blueprint $table) => $table->dropColumn($column));
            }
        }
    }
};
