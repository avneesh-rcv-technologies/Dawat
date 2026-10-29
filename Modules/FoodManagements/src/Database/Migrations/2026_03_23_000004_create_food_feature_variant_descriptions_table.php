<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('food_feature_variant_descriptions', function (Blueprint $table) {
            $table->unsignedBigInteger('variant_id');
            $table->char('lang_code', 2)->default('en');

            $table->string('description', 255)->default('');

            $table->primary(['variant_id', 'lang_code']);
            $table->index('lang_code');

            $table->foreign('variant_id')
                ->references('variant_id')
                ->on('food_feature_variants')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('food_feature_variant_descriptions');
    }
};
