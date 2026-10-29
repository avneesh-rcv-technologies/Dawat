<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('food_option_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Extras, Sauces
            $table->boolean('is_required')->default(false);
            $table->integer('min')->default(0);
            $table->integer('max')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('food_option_groups');
    }
};
