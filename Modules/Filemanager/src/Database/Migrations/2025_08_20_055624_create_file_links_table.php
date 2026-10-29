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
       Schema::create('file_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('file_id')->constrained()->onDelete('cascade');
            $table->string('object_type', 50);
            $table->unsignedBigInteger('object_id');
            $table->enum('type', ['M', 'A'])->default('M');
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
            
            // Indexes
            $table->index(['object_type', 'object_id']);
            $table->index('type');
            $table->index('position');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('file_links');
    }
};
