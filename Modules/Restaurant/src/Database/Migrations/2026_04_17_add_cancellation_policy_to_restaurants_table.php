<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('restaurants', function (Blueprint $table) {
            // Allow order cancellation by customers
            $table->boolean('allow_order_cancellation')
                ->default(true)
                ->after('order_time_limit')
                ->comment('Allow customers to cancel orders');

            // Time limit in minutes after order placement
            $table->integer('cancellation_time_limit')
                ->default(15)
                ->after('allow_order_cancellation')
                ->comment('Minutes after order placement when cancellation is allowed');

            // Which order statuses allow cancellation (JSON array)
            $table->json('cancellable_statuses')
                ->nullable()
                ->after('cancellation_time_limit')
                ->comment('Order statuses that allow cancellation');

            // Auto refund policy
            $table->boolean('auto_refund_on_cancellation')
                ->default(true)
                ->after('cancellable_statuses')
                ->comment('Automatically process refunds on cancellation');

            // Cancellation fee percentage (0-100)
            $table->decimal('cancellation_fee_percentage', 5, 2)
                ->default(0)
                ->after('auto_refund_on_cancellation')
                ->comment('Cancellation fee as percentage of order total');
        });
    }

    public function down(): void
    {
        Schema::table('restaurants', function (Blueprint $table) {
            $table->dropColumn([
                'allow_order_cancellation',
                'cancellation_time_limit',
                'cancellable_statuses',
                'auto_refund_on_cancellation',
                'cancellation_fee_percentage'
            ]);
        });
    }
};
