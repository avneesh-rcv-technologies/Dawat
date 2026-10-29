<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('plan_features', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_id')->constrained('plans')->onDelete('cascade');
            $table->foreignId('feature_id')->constrained('features')->onDelete('cascade');
            
            // Feature value/configuration for this plan
            $table->string('value')->comment('Feature value: "true/false" for boolean, numeric for limit, tier name for package');
            
            // Additional notes specific to this plan-feature combination
            $table->text('notes')->nullable()->comment('Additional notes or restrictions');
            
            // Display configuration
            $table->boolean('is_highlighted')->default(false)->comment('Highlight this feature in plan comparison');
            $table->integer('sort_order')->default(0)->comment('Override feature sort order for this plan');
            
            $table->timestamps();

            // Composite unique key - a plan can have each feature only once
            $table->unique(['plan_id', 'feature_id']);
            
            // Indexes
            $table->index('plan_id');
            $table->index('feature_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plan_features');
    }
};
