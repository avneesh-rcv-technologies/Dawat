<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('currencies', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->string('name');
            $table->string('symbol')->nullable();
            $table->unsignedInteger('decimal');
            $table->decimal('rate', 24, 12)->unsigned()->default(2);
            $table->string('group_separator')->default(',');
            $table->string('decimal_separator')->default('.');
            $table->string('currency_position')->nullable();
            $table->boolean('default_currency')->default(false);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('currencies');
    }
};