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
        Schema::create('subscription_reminders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('subscription_id');
            $table->string('type')->default('expiry'); // expiry, payment, renewal, etc
            $table->string('status')->default('pending'); // pending, sent, failed, skipped
            $table->text('message')->nullable();
            $table->text('subject')->nullable();
            $table->string('channel')->default('email'); // email, in-app, sms
            $table->integer('reminder_number')->default(1); // which reminder this is (1st, 2nd, etc)
            $table->dateTime('scheduled_at')->nullable();
            $table->dateTime('sent_at')->nullable();
            $table->string('error_message')->nullable();
            $table->integer('retry_count')->default(0);
            $table->dateTime('last_retry_at')->nullable();
            $table->json('metadata')->nullable(); // additional data
            $table->timestamps();
            
            // Indexes
            $table->foreign('subscription_id')
                  ->references('id')
                  ->on('subscriptions')
                  ->onDelete('cascade');
            $table->index('status');
            $table->index('type');
            $table->index('scheduled_at');
            $table->index('sent_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_reminders');
    }
};
