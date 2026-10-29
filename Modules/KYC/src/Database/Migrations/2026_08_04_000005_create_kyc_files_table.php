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
        Schema::create('kyc_files', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('submission_id');
            $table->unsignedBigInteger('field_id');
            $table->string('file_path', 255);
            $table->string('file_type', 100);
            $table->timestamp('uploaded_at')->useCurrent();
            
            $table->foreign('submission_id')
                ->references('id')
                ->on('kyc_submissions')
                ->onDelete('cascade');
                
            $table->foreign('field_id')
                ->references('id')
                ->on('kyc_fields')
                ->onDelete('cascade');
                
            $table->index(['submission_id', 'field_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kyc_files');
    }
};
