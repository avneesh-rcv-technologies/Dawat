<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('mobile_features', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->enum('used_for', ['general', 'carousel'])->default('general');
            $table->enum('type', ['product', 'category'])->default('product');
            $table->enum('selection_type', ['newest', 'oldest', 'custom'])->default('newest');
            $table->json('custom_items')->nullable(); // Store selected product/category IDs
            $table->integer('items_count')->default(10);
            $table->integer('position')->default(0);
            $table->boolean('status')->default(1);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('mobile_features');
    }
};