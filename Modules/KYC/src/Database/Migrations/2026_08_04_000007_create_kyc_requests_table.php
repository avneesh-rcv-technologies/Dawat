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
        Schema::create('kyc_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('kyc_type_id');
            $table->unsignedBigInteger('requested_by');
            $table->string('title', 255);
            $table->text('message')->nullable();
            $table->date('due_date')->nullable();
            $table->timestamp('created_at')->useCurrent();
            
            $table->foreign('kyc_type_id')
                ->references('id')
                ->on('kyc_types')
                ->onDelete('cascade');
                
            $table->foreign('requested_by')
                ->references('id')
                ->on('admins')
                ->onDelete('cascade');
                
            $table->index('due_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kyc_requests');
    }
};
