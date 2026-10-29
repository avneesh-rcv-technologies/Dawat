<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('restaurants', function (Blueprint $table) {
            if (Schema::hasColumn('restaurants', 'auto_refund_on_cancellation')) {
                $table->dropColumn('auto_refund_on_cancellation');
            }
            if (Schema::hasColumn('restaurants', 'cancellation_fee_percentage')) {
                $table->dropColumn('cancellation_fee_percentage');
            }
        });
    }

    public function down(): void
    {
        Schema::table('restaurants', function (Blueprint $table) {
            $table->boolean('auto_refund_on_cancellation')
                ->default(true)
                ->after('cancellable_statuses')
                ->comment('Automatically process refunds on cancellation');
            
            $table->decimal('cancellation_fee_percentage', 5, 2)
                ->default(0)
                ->after('auto_refund_on_cancellation')
                ->comment('Cancellation fee as percentage of order total');
        });
    }
};
