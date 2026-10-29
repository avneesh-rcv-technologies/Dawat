<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('restaurant_onboarding_status_histories', function (Blueprint $table) {
            $table->string('step_key', 50)->nullable()->index();
            $table->string('action', 50)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('restaurant_onboarding_status_histories', function (Blueprint $table) {
            $table->dropIndex(['step_key']);
            $table->dropColumn(['step_key', 'action']);
        });
    }
};
