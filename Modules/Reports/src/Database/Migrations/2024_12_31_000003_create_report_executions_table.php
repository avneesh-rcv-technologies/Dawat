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
        Schema::create('report_executions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_id')->constrained('reports')->onDelete('cascade');
            $table->morphs('executed_by'); // creates executed_by_id and executed_by_type
            $table->foreignId('schedule_id')->nullable()->constrained('report_schedules')->onDelete('set null');
            
            // Execution details
            $table->string('status'); // pending, running, completed, failed
            $table->integer('rows_returned')->nullable();
            $table->integer('execution_time_ms')->nullable(); // Execution time in milliseconds
            $table->text('error_message')->nullable();
            $table->json('applied_filters')->nullable(); // Filters used in this execution
            
            // Result caching
            $table->string('result_hash', 64)->nullable(); // Hash of result for caching (changed from text to string)
            $table->boolean('is_cached')->default(false);
            $table->timestamp('cache_expires_at')->nullable();
            
            // Metadata
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index('report_id');
            $table->index(['executed_by_id', 'executed_by_type']);
            $table->index('status');
            $table->index('created_at');
            $table->index('result_hash'); // Now can be indexed as varchar
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('report_executions');
    }
};
