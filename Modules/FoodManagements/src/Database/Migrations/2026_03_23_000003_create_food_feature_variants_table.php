<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('food_feature_variants', function (Blueprint $table) {
            $table->bigIncrements('variant_id');
            $table->unsignedBigInteger('feature_id');

            $table->unsignedSmallInteger('position')->default(0);
            $table->char('status', 1)->default('A');

            $table->timestamps();

            $table->index('feature_id');
            $table->index('status');
            $table->index('position');

            $table->foreign('feature_id')
                ->references('feature_id')
                ->on('food_features')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('food_feature_variants');
    }
};
