<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('food_options')) {
            Schema::create('food_options', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('food_option_group_id');
                $table->string('name');
                $table->decimal('price', 8, 2)->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                // Foreign key constraint
                $table->foreign('food_option_group_id')->references('id')->on('food_option_groups')->onDelete('cascade');
                
                // Index for better performance
                $table->index('food_option_group_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('food_options');
    }
};