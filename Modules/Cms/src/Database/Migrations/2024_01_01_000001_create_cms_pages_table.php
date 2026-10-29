<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('cms_page_settings', function (Blueprint $table) {
            $table->id();
            $table->string('page_title');
            $table->longText('description');
            $table->string('slug')->unique();
            $table->boolean('status')->default(1);
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->text('meta_keywords')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('cms_pages');
    }
};