<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('food_features', function (Blueprint $table) {
            $table->bigIncrements('feature_id');

            $table->string('feature_code', 32)->default('');
            $table->string('purpose', 32)->default('');
            $table->string('feature_style', 32)->default('');
            $table->string('filter_style', 32)->default('');

            // T = text, S = select, M = multiselect, N = number, G = group, etc.
            $table->char('feature_type', 1)->default('T');

            // 0 = no parent group
            $table->unsignedBigInteger('parent_id')->default(0);

            $table->char('display_on_product', 1)->default('Y');
            $table->char('display_on_catalog', 1)->default('Y');
            $table->char('display_on_header', 1)->default('N');

            $table->char('status', 1)->default('A');
            $table->unsignedSmallInteger('position')->default(0);
            $table->char('comparison', 1)->default('N');

            $table->timestamps();

            $table->index('status');
            $table->index('parent_id');
            $table->index('feature_type');
            $table->index('position');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('food_features');
    }
};
