<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('notification_mappings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('notification_events');
            $table->foreignId('channel_id')->constrained('notification_channels');
            $table->string('template_id')->nullable();
            $table->json('custom_config')->nullable(); // Channel-specific customizations
            $table->integer('priority')->default(0); // For ordering delivery
            $table->timestamps();
            $table->unique(['event_id', 'channel_id']); // Prevent duplicate mappings
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_mappings');
    }
};
