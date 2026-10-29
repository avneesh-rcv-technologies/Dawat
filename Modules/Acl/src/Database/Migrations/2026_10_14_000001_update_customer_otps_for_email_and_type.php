<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customer_otps', function (Blueprint $table) {
            $table->string('email')->nullable()->after('phone')->index();
            $table->string('type')->nullable()->default('login')->after('purpose');
            $table->string('channel_type')->nullable()->default('sms')->after('type');
            $table->index(['email', 'type']);
            $table->index(['phone', 'type']);
            $table->index(['channel_type', 'is_verified']);
            $table->string('phone', 20)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('customer_otps', function (Blueprint $table) {
            $table->dropIndex(['email', 'type']);
            $table->dropIndex(['phone', 'type']);
            $table->dropIndex(['channel_type', 'is_verified']);
            $table->dropColumn(['email', 'type', 'channel_type']);
        });
    }
};
