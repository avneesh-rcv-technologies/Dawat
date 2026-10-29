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
        Schema::create('ratings', function (Blueprint $table) {
            $table->id();
            $table->string('ratable_type'); // The type of model being rated (e.g., Product, Service)
            $table->unsignedBigInteger('ratable_id'); // The ID of the model being rated
            $table->unsignedBigInteger('user_id')->nullable(); // User who gave the rating (null for guest)
            $table->string('guest_name')->nullable(); // Name of guest reviewer
            $table->string('guest_email')->nullable(); // Email of guest reviewer
            $table->integer('rating'); // Rating value (1-5)
            $table->text('review')->nullable(); // Review text
            $table->string('ip_address')->nullable(); // IP address of reviewer
            $table->boolean('is_approved')->default(false); // Approval status
            $table->boolean('status')->default(1); // Active/Inactive status
            $table->timestamps();
            
            $table->index(['ratable_type', 'ratable_id']);
            $table->index('user_id');
            $table->index('is_approved');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ratings');
    }
};
