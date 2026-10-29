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
        Schema::dropIfExists('report_saved_filters');
        Schema::dropIfExists('report_exports');
        Schema::dropIfExists('report_executions');
        Schema::dropIfExists('report_schedules');
        Schema::dropIfExists('reports');
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            
            // Data source configuration
            $table->string('data_source'); // table or model name
            $table->string('data_source_type')->default('table'); // table, model, custom_query
            $table->text('custom_query')->nullable(); // For advanced users
            
            // Report configuration
            $table->json('columns')->nullable(); // Selected columns with config
            $table->json('filters')->nullable(); // Filter conditions
            $table->string('filter_logic')->default('AND'); // AND, OR, custom
            $table->json('aggregations')->nullable(); // SUM, COUNT, AVG, etc.
            $table->json('group_by')->nullable(); // GROUP BY columns
            $table->json('order_by')->nullable(); // Sorting configuration
            $table->integer('limit')->nullable(); // Result limit
            
            // Visualization
            $table->string('chart_type')->default('table'); // table, bar, line, pie, area, donut
            $table->json('chart_config')->nullable(); // Chart-specific settings
            
            // Access control (polymorphic creator)
            $table->morphs('created_by'); // creates created_by_id and created_by_type
            $table->boolean('is_public')->default(false); // Accessible to all admins
            $table->json('allowed_roles')->nullable(); // Specific roles that can access
            $table->json('allowed_users')->nullable(); // Specific users that can access
            
            // Status and metadata
            $table->boolean('is_active')->default(true);
            $table->boolean('is_scheduled')->default(false);
            $table->integer('execution_count')->default(0);
            $table->timestamp('last_executed_at')->nullable();
            $table->json('metadata')->nullable(); // Additional settings
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('slug');
            $table->index('data_source');
            $table->index(['created_by_id', 'created_by_type']);
            $table->index('is_active');
            $table->index('is_scheduled');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};

