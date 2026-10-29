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
        Schema::create('report_exports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_id')->constrained('reports')->onDelete('cascade');
            $table->foreignId('execution_id')->nullable()->constrained('report_executions')->onDelete('set null');
            $table->morphs('exported_by'); // creates exported_by_id and exported_by_type
            // Export details
            $table->string('format'); // csv, excel, pdf
            $table->string('filename');
            $table->string('file_path')->nullable();
            $table->integer('file_size')->nullable(); // Size in bytes
            $table->string('status')->default('pending'); // pending, processing, completed, failed
            
            // Export configuration
            $table->json('export_options')->nullable();
            $table->integer('rows_exported')->nullable();
            
            // Download tracking
            $table->integer('download_count')->default(0);
            $table->timestamp('last_downloaded_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index('report_id');
            $table->index(['exported_by_id', 'exported_by_type']);
            $table->index('status');
            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('report_exports');
    }
};
