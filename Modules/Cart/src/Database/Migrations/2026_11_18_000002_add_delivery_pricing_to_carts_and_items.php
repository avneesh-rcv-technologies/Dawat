<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('carts', function (Blueprint $table) {
            $table->unsignedBigInteger('delivery_address_id')->nullable()->index();
            $table->decimal('delivery_fee', 10, 2)->nullable();
            $table->decimal('delivery_distance_miles', 12, 4)->nullable();
            $table->boolean('delivery_available')->nullable();
            $table->string('delivery_unavailable_reason')->nullable();
            $table->unsignedInteger('estimated_delivery_minutes')->nullable();
            $table->timestamp('estimated_delivery_at')->nullable();
            $table->timestamp('delivery_calculated_at')->nullable();
        });
        Schema::table('cart_items', function (Blueprint $table) {
            $table->decimal('delivery_fee', 10, 2)->nullable();
            $table->unsignedInteger('estimated_delivery_minutes')->nullable();
            $table->timestamp('estimated_delivery_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('cart_items', fn (Blueprint $table) => $table->dropColumn([
            'delivery_fee', 'estimated_delivery_minutes', 'estimated_delivery_at',
        ]));
        Schema::table('carts', function (Blueprint $table) {
            $table->dropIndex(['delivery_address_id']);
            $table->dropColumn([
                'delivery_address_id', 'delivery_fee', 'delivery_distance_miles', 'delivery_available',
                'delivery_unavailable_reason', 'estimated_delivery_minutes', 'estimated_delivery_at', 'delivery_calculated_at',
            ]);
        });
    }
};
