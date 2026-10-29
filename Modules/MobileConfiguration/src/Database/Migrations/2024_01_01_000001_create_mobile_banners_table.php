<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('mobile_banners', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['product', 'category', 'external_link', 'no_link'])->default('no_link');
            $table->string('link_value')->nullable(); // product_id, category_id, or external URL
            $table->boolean('status')->default(1);
            $table->integer('position')->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('mobile_banners');
    }
};