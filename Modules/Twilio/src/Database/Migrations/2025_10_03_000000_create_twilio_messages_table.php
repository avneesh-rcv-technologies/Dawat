<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('twilio_messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('phone_number_id');
            $table->unsignedBigInteger('admin_id')->nullable();
            $table->string('to')->nullable();
            $table->text('body')->nullable();
            $table->string('delivery_sid')->nullable();
            $table->string('status')->nullable();
            $table->text('error')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('response_at')->nullable();
            $table->text('response')->nullable();
            $table->unsignedTinyInteger('retry_count')->default(0);
            $table->timestamps();

            $table->foreign('phone_number_id')->references('id')->on('twilio_phone_numbers')->onDelete('cascade');
        });

        // Add a send_count column to phone numbers for quick counts
        if (Schema::hasTable('twilio_phone_numbers')) {
            Schema::table('twilio_phone_numbers', function (Blueprint $table) {
                if (!Schema::hasColumn('twilio_phone_numbers', 'send_count')) {
                    $table->unsignedInteger('send_count')->default(0)->after('retry_count');
                }
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('twilio_messages')) {
            Schema::dropIfExists('twilio_messages');
        }

        if (Schema::hasTable('twilio_phone_numbers') && Schema::hasColumn('twilio_phone_numbers', 'send_count')) {
            Schema::table('twilio_phone_numbers', function (Blueprint $table) {
                $table->dropColumn('send_count');
            });
        }
    }
};
