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
        if (!Schema::hasTable('twilio_messaging_automation')) {
            Schema::create('twilio_messaging_automation', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('admin_id');
                $table->string('keyword');
                $table->enum('response_type', ['text', 'chatgpt'])->default('text');
                $table->text('response_text')->nullable();
                $table->boolean('is_active')->default(true);
                $table->integer('priority')->default(0);
                $table->boolean('use_chatgpt')->default(false);
                $table->text('chatgpt_prompt')->nullable();
                $table->boolean('case_sensitive')->default(false);
                $table->boolean('exact_match')->default(false);
                $table->timestamps();

                $table->foreign('admin_id')->references('id')->on('admins')->onDelete('cascade');
                $table->index(['is_active', 'priority']);
                $table->index('keyword');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('twilio_messaging_automation');
    }
};
