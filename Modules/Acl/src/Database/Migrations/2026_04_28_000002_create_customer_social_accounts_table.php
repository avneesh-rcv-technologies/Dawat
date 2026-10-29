<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_social_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->enum('provider', ['google', 'apple'])->index();
            $table->string('provider_id')->index();
            $table->string('provider_email')->nullable();
            $table->text('provider_token')->nullable();
            $table->text('provider_refresh_token')->nullable();
            $table->timestamp('token_expires_at')->nullable();
            $table->json('provider_data')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->timestamps();
            
            // Unique constraints
            $table->unique(['customer_id', 'provider']);
            $table->unique(['provider', 'provider_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_social_accounts');
    }
};
