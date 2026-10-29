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
        Schema::create('report_saved_filters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_id')->constrained('reports')->onDelete('cascade');
            $table->morphs('user'); // creates user_id and user_type
            
            $table->string('name');
            $table->json('filters'); // Saved filter configuration
            $table->boolean('is_default')->default(false);
            $table->boolean('is_public')->default(false); // Share with other users
            
            $table->timestamps();
            
            // Indexes
            $table->index('report_id');
            $table->index(['user_id', 'user_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('report_saved_filters');
    }
};
