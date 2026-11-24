
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedInteger('event_guest_count')->nullable();
            $table->decimal('delivery_distance_miles', 12, 4)->nullable();
            $table->unsignedInteger('estimated_delivery_minutes')->nullable();
            $table->boolean('is_tax_inclusive')->nullable();
            $table->json('tax_breakdown')->nullable();
            $table->json('discount_breakdown')->nullable();
            $table->json('delivery_policy_snapshot')->nullable();
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->unsignedBigInteger('serving_option_id')->nullable();
            $table->string('serve_type', 30)->nullable();
            $table->string('serving_name', 100)->nullable();
            $table->unsignedInteger('guest_count')->nullable();
            $table->json('selected_option_snapshot')->nullable();
            $table->decimal('tax_amount', 10, 2)->nullable();
            $table->decimal('discount_amount', 10, 2)->nullable();
            $table->decimal('final_price', 10, 2)->nullable();
            $table->boolean('is_tax_inclusive')->nullable();
            $table->json('tax_breakdown')->nullable();
            $table->decimal('delivery_fee', 10, 2)->nullable();
            $table->unsignedInteger('estimated_delivery_minutes')->nullable();
            $table->dateTime('estimated_delivery_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn([
                'serving_option_id',
                'serve_type',
                'serving_name',
                'guest_count',
                'selected_option_snapshot',
                'tax_amount',
                'discount_amount',
                'final_price',
                'is_tax_inclusive',
                'tax_breakdown',
                'delivery_fee',
                'estimated_delivery_minutes',
                'estimated_delivery_at',
            ]);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'event_guest_count',
                'delivery_distance_miles',
                'estimated_delivery_minutes',
                'is_tax_inclusive',
                'tax_breakdown',
                'discount_breakdown',
                'delivery_policy_snapshot',
            ]);
        });
    }
};
