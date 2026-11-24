<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seo_pages', function (Blueprint $table) {
            $table->id();
            $table->string('page_key')->unique();
            $table->string('route_name')->nullable();
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->string('keywords')->nullable();
            $table->string('robots')->nullable();
            $table->string('og_image')->nullable();
            $table->string('twitter_image')->nullable();
            $table->json('schema')->nullable();
            $table->string('canonical')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('route_name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seo_pages');
    }
};
