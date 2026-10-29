<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('web_push_subscriptions', function (Blueprint $table) {
            $table->string('device')->nullable()->after('browser');
        });
    }

    public function down(): void
    {
        Schema::table('web_push_subscriptions', function (Blueprint $table) {
            $table->dropColumn('device');
        });
    }
};
