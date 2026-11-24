<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('request_activity_logs_archive')) {
            return;
        }

        Schema::create('request_activity_logs_archive', function (Blueprint $table) {
            $table->id();
            $table->string('ip_address', 45)->nullable()->index();
            $table->string('guard')->nullable();
            $table->string('causer_type')->nullable();
            $table->unsignedBigInteger('causer_id')->nullable()->index();
            $table->string('method', 10)->nullable();
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
            $table->boolean('is_blocked')->default(false);
            $table->string('blocked_reason')->nullable();
            $table->json('response_headers')->nullable();
            $table->longText('response_body')->nullable();
            $table->timestamp('archived_at')->nullable()->index();
            $table->timestamps();
            $table->index(['created_at', 'ip_address']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('request_activity_logs_archive');
    }
};
