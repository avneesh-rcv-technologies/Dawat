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
        Schema::create('report_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_id')->constrained('reports')->onDelete('cascade');
            
            // Schedule configuration
            $table->string('frequency'); // daily, weekly, monthly, custom
            $table->string('cron_expression')->nullable(); // For custom schedules
            $table->time('scheduled_time')->default('09:00:00');
            $table->json('scheduled_days')->nullable(); // For weekly schedules
            $table->integer('scheduled_date')->nullable(); // For monthly schedules
            
            // Delivery configuration
            $table->json('recipients'); // Email addresses
            $table->string('export_format')->default('pdf'); // pdf, excel, csv
            $table->text('email_subject')->nullable();
            $table->text('email_body')->nullable();
            
            // Status
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_run_at')->nullable();
            $table->timestamp('next_run_at')->nullable();
            $table->integer('run_count')->default(0);
            $table->integer('failure_count')->default(0);
            
            $table->timestamps();
            
            // Indexes
            $table->index('report_id');
            $table->index('is_active');
            $table->index('next_run_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('report_schedules');
    }
};
