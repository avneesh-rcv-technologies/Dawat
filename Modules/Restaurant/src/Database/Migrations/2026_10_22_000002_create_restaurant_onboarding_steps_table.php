<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('restaurant_onboarding_steps', function (Blueprint $table) {
            $table->id();

            $table->foreignId('restaurant_id')
                ->constrained('restaurants')
                ->cascadeOnDelete();

            $table->string('step_key', 50);

            $table->string('status', 30)
                ->default('draft');

            $table->text('rejection_reason')->nullable();

            $table->timestamp('submitted_at')->nullable();

            $table->foreignId('reviewed_by')
                ->nullable()
                ->constrained('admins')
                ->nullOnDelete();

            $table->timestamp('reviewed_at')->nullable();

            $table->timestamps();

            $table->unique(
                ['restaurant_id', 'step_key'],
                'restaurant_onboarding_steps_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('restaurant_onboarding_steps');
    }
};
