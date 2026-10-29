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
        Schema::create('kyc_submissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id'); // No foreign key as per requirement
            $table->unsignedBigInteger('kyc_type_id');
            $table->enum('status', ['pending', 'under_review', 'approved', 'rejected'])
                ->default('pending');
            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->datetime('reviewed_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
            
            $table->foreign('kyc_type_id')
                ->references('id')
                ->on('kyc_types')
                ->onDelete('cascade');
                
            $table->foreign('reviewed_by')
                ->references('id')
                ->on('admins')
                ->onDelete('set null');
                
            $table->index('user_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kyc_submissions');
    }
};
