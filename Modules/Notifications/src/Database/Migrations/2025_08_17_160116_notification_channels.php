<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('notification_channels', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., "Email", "SMS", "Slack", "Push"
            $table->string('channel_class'); // Fully qualified class name for the channel implementation
            $table->boolean('status')->default(true); // Active/inactive
            $table->json('config')->nullable(); // Channel-specific configuration
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_channels');
    }
};
