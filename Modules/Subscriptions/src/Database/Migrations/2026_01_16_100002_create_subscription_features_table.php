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
        Schema::create('subscription_features', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscription_id')->constrained('subscriptions')->onDelete('cascade');
            $table->foreignId('feature_id')->constrained('features')->onDelete('cascade');
            
            // Feature value assigned to this subscription
            $table->string('value')->comment('Feature value from plan at time of subscription');
            
            // Usage tracking for limit-based features
            $table->decimal('used', 15, 2)->default(0)->comment('Current usage for limit-based features');
            $table->timestamp('usage_reset_at')->nullable()->comment('When usage counter resets (for monthly limits, etc.)');
            
            // Override capability
            $table->boolean('is_custom')->default(false)->comment('Whether this is a custom override value');
            $table->text('custom_notes')->nullable()->comment('Notes for custom overrides');
            
            // Status
            $table->boolean('is_active')->default(true)->comment('Whether feature is currently active');
            
            $table->timestamps();

            // Composite unique key
            $table->unique(['subscription_id', 'feature_id']);
            
            // Indexes
            $table->index('subscription_id');
            $table->index('feature_id');
            $table->index(['subscription_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_features');
    }
};
