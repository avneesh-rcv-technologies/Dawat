<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('restaurants', function (Blueprint $table) {
            $table->unsignedTinyInteger('onboarding_step')
                ->default(1)
                ->after('onboarding_status');

            $table->timestamp('submitted_at')
                ->nullable()
                ->after('onboarding_step');
        });
    }

    public function down(): void
    {
        Schema::table('restaurants', function (Blueprint $table) {
            $table->dropColumn([
                'onboarding_step',
                'submitted_at',
            ]);
        });
    }
};
