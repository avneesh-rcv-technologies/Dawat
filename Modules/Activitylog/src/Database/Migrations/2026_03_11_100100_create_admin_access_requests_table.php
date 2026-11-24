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
        Schema::create('admin_access_requests', function (Blueprint $table) {
            $table->id();
            $table->string('ip_address', 45)->index();
            $table->text('requested_route')->nullable();
            $table->text('reason');
            $table->string('status')->default('pending')->index();

            $table->unsignedBigInteger('requested_by_id')->nullable();
            $table->string('requested_by_type')->nullable();

            $table->unsignedBigInteger('reviewed_by_id')->nullable();
            $table->string('reviewed_by_type')->nullable();
            $table->text('review_note')->nullable();
            $table->timestamp('reviewed_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_access_requests');
    }
};
