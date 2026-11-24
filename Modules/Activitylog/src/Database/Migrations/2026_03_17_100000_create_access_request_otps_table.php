<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('access_request_otps', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('admin_access_request_id')->nullable()->index();
            $table->string('ip_address', 45)->index();
            $table->string('request_token')->unique();
            $table->string('otp_hash');
            $table->timestamp('expires_at')->index();
            $table->timestamp('used_at')->nullable();
            $table->timestamps();

            $table->foreign('admin_access_request_id')
                ->references('id')
                ->on('admin_access_requests')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('access_request_otps');
    }
};
