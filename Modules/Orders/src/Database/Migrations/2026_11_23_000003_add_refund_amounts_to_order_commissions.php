
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_commissions', function (Blueprint $table) {
            $table->decimal('refunded_amount', 12, 2)->default(0);
            $table->decimal('commission_reversed_amount', 12, 2)->default(0);
            $table->decimal('restaurant_refund_deduction', 12, 2)->default(0);
            $table->decimal('final_payable_amount', 12, 2)->default(0);
            $table->decimal('paid_amount', 12, 2)->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('order_commissions', function (Blueprint $table) {
            $table->dropColumn([
                'refunded_amount',
                'commission_reversed_amount',
                'restaurant_refund_deduction',
                'final_payable_amount',
                'paid_amount',
            ]);
        });
    }
};
