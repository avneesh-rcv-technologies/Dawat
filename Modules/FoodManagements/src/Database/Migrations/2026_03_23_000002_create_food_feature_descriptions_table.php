<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('food_feature_descriptions', function (Blueprint $table) {
            $table->unsignedBigInteger('feature_id');
            $table->char('lang_code', 2)->default('en');

            $table->string('name', 255)->default('');
            $table->mediumText('description')->nullable();

            $table->primary(['feature_id', 'lang_code']);
            $table->index('lang_code');

            $table->foreign('feature_id')
                ->references('feature_id')
                ->on('food_features')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('food_feature_descriptions');
    }
};
