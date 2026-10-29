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
        Schema::create('kyc_submission_data', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('submission_id');
            $table->unsignedBigInteger('field_id');
            $table->string('field_key', 150);
            $table->text('field_value')->nullable();
            $table->boolean('verified')->default(0);
            $table->datetime('verified_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->foreign('submission_id')
                ->references('id')
                ->on('kyc_submissions')
                ->onDelete('cascade');
                
            $table->foreign('field_id')
                ->references('id')
                ->on('kyc_fields')
                ->onDelete('cascade');
                
            $table->unique(['submission_id', 'field_id'], 'unique_submission_field');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kyc_submission_data');
    }
};
