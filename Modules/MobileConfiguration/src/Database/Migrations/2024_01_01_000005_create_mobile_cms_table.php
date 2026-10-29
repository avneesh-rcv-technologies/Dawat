<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('mobile_cms', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('cms_page_id');
            $table->boolean('status')->default(1);
            $table->integer('position')->default(0);
            $table->timestamps();

            $table->foreign('cms_page_id')->references('id')->on('cms_page_settings')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('mobile_cms');
    }
};