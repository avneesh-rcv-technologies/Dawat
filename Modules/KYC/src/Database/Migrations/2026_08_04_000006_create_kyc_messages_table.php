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
        Schema::create('kyc_messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('submission_id');
            $table->enum('sender_type', ['user', 'admin']);
            $table->unsignedBigInteger('sender_id');
            $table->text('message');
            $table->boolean('is_read')->default(0);
            $table->timestamp('created_at')->useCurrent();
            
            $table->foreign('submission_id')
                ->references('id')
                ->on('kyc_submissions')
                ->onDelete('cascade');
                
            $table->index(['submission_id', 'sender_type']);
            $table->index('is_read');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kyc_messages');
    }
};
