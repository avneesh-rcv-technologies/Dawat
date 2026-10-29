<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_otps', function (Blueprint $table) {
            $table->id();
            $table->string('phone', 20)->index();
            $table->string('otp', 4);
            $table->enum('purpose', ['login', 'register', 'verify_phone'])->default('login');
            $table->timestamp('expires_at');
            $table->boolean('is_verified')->default(false);
            $table->timestamp('verified_at')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->unsignedInteger('attempts')->default(0);
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['phone', 'is_verified', 'expires_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_otps');
    }
};
