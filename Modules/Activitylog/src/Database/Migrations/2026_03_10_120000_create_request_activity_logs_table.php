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
        Schema::create('request_activity_logs', function (Blueprint $table) {
            $table->id();
            $table->string('ip_address', 45)->nullable()->index();
            $table->string('guard')->nullable();
            $table->string('causer_type')->nullable();
            $table->unsignedBigInteger('causer_id')->nullable()->index();

            $table->string('method', 10)->nullable()->index();
            $table->text('url')->nullable();
            $table->text('path')->nullable();
            $table->string('route_name')->nullable();
            $table->text('route_action')->nullable();

            $table->json('query_params')->nullable();
            $table->json('request_body')->nullable();
            $table->json('headers')->nullable();
            $table->text('user_agent')->nullable();
            $table->text('referer')->nullable();

            $table->json('request_middleware')->nullable();
            $table->json('web_middleware_map')->nullable();
            $table->unsignedSmallInteger('response_status')->nullable()->index();
            $table->unsignedInteger('duration_ms')->nullable();

            $table->boolean('is_blocked')->default(false)->index();
            $table->string('blocked_reason')->nullable();

            $table->timestamps();
            $table->index(['created_at', 'ip_address']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('request_activity_logs');
    }
};
