<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   
    public function up(): void
    {
        Schema::create('blogs', function (Blueprint $table) {
            $table->id(); 
            $table->string('title');
            $table->string('short_description')->nullable(); 
            $table->text('long_description')->nullable(); 
            $table->string('inner_image')->nullable(); 
            $table->string('outer_image')->nullable(); 
            $table->string('author_name')->nullable(); 
            $table->string('meta_title')->nullable(); 
            $table->string('meta_description')->nullable();
            $table->string('meta_keywords')->nullable(); 
            $table->string('post_url')->unique(); 
            $table->string('locale')->default('en'); 
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft'); 
            $table->integer('sort_order')->default(0); 
            $table->timestamps(); 
        });
    }

    
    public function down(): void
    {
        Schema::dropIfExists('blogs');
    }
};
