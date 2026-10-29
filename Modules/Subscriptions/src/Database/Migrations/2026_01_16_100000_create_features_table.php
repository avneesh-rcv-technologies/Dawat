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
        Schema::create('features', function (Blueprint $table) {
            $table->id();
            $table->string('name', 128)->unique()->comment('Unique feature name e.g., "api_calls", "storage_gb", "users"');
            $table->string('display_name', 255)->comment('Human-readable feature name');
            $table->text('description')->nullable()->comment('Feature description');
            
            // Feature type determines how it's used
            $table->enum('type', ['boolean', 'limit', 'package'])
                  ->default('boolean')
                  ->comment('boolean: yes/no, limit: numeric value, package: named tier');
            
            // Resource configuration
            $table->string('unit')->nullable()->comment('Unit of measurement: GB, requests, users, etc.');
            $table->json('metadata')->nullable()->comment('Additional configuration: min, max, step values, etc.');
            
            // Default value for features
            $table->string('default_value')->nullable()->comment('Default value if not specified in plan');
            
            // Display and grouping
            $table->string('group')->nullable()->comment('Group name for organizing features: Storage, API, Users, etc.');
            $table->integer('sort_order')->default(0)->comment('Display order within group');
            
            // Status
            $table->boolean('is_active')->default(true)->comment('Whether feature is currently available');
            
            $table->timestamps();

            // Indexes
            $table->index('is_active');
            $table->index('group');
            $table->index(['group', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('features');
    }
};
