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
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            
            // Polymorphic relationship (can be user, admin, organization, etc.)
            $table->morphs('subscribable');
            
            // Plan reference
            $table->foreignId('plan_id')->constrained('plans')->onDelete('restrict');
            
            // Subscription status
            $table->enum('status', ['trialing', 'active', 'past_due', 'paused', 'canceled', 'expired'])
                  ->default('active')
                  ->comment('Current subscription status');
            
            // Subscription period
            $table->timestamp('start_date')->nullable()->comment('Subscription start date');
            $table->timestamp('trial_ends_at')->nullable()->comment('Trial end date');
            $table->timestamp('current_period_start')->nullable()->comment('Current billing period start');
            $table->timestamp('current_period_end')->nullable()->comment('Current billing period end');
            $table->timestamp('ends_at')->nullable()->comment('When subscription ends (for cancellations)');
            
            // Cancellation flags
            $table->boolean('cancel_at_period_end')->default(false)->comment('Cancel at end of current period');
            $table->timestamp('canceled_at')->nullable()->comment('When subscription was canceled');
            
            // Payment gateway references
            $table->string('gateway_subscription_id')->nullable()->comment('External subscription ID from payment gateway');
            $table->morphs('gateway_customer'); // Polymorphic relation to customer (User, Admin, etc.)
            $table->string('payment_gateway')->nullable()->comment('Payment gateway used: stripe, paypal, razorpay, etc.');
            
            // Additional metadata
            $table->json('metadata')->nullable()->comment('Additional subscription data');
            $table->text('notes')->nullable()->comment('Internal notes');
            
            $table->softDeletes();
            $table->timestamps();

            // Indexes (note: morphs() already creates index for subscribable_type and subscribable_id)
            $table->index('status');
            $table->index('plan_id');
            $table->index('current_period_end');
            $table->index('gateway_subscription_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
