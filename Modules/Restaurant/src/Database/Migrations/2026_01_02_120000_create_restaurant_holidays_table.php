<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('restaurant_holidays', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('restaurant_id');
            $table->date('date');
            $table->string('reason', 255)->nullable();
            $table->boolean('is_closed')->default(true);
            $table->timestamps();

            $table->foreign('restaurant_id')
                ->references('id')
                ->on('restaurants')
                ->onDelete('cascade');

            $table->unique(['restaurant_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('restaurant_holidays');
    }
};