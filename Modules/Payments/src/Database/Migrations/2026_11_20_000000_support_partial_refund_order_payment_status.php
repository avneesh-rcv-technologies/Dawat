<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Older installations use an enum that cannot store partially_refunded.
        Schema::table('orders', function (Blueprint $table) {
            $table->string('payment_status', 32)->default('pending')->change();
        });
    }

    public function down(): void
    {
        // Preserve existing payment statuses; narrowing back to the legacy enum would lose data.
    }
};
