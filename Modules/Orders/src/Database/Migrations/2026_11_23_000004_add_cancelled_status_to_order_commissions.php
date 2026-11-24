<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_commissions', function (Blueprint $table) {
            $table->enum('status', ['pending', 'on_hold', 'processed', 'cancelled', 'failed'])->default('pending')->change();
        });
    }

    public function down(): void
    {
        DB::table('order_commissions')->where('status', 'cancelled')->update(['status' => 'on_hold']);
        Schema::table('order_commissions', function (Blueprint $table) {
            $table->enum('status', ['pending', 'on_hold', 'processed', 'failed'])->default('pending')->change();
        });
    }
};
