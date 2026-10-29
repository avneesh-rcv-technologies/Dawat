<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('twilio_phone_numbers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('upload_id'); // Foreign key to twilio_phone_uploads
            $table->unsignedBigInteger('admin_id'); // Foreign key to admins
            $table->string('phone_number');
            $table->string('status')->default('pending'); // sent, failed, opted-out, etc.
            $table->string('delivery_sid')->nullable();
            $table->text('message')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('response_at')->nullable();
            $table->text('response')->nullable();
            $table->unsignedTinyInteger('retry_count')->default(0);
            $table->timestamps();

            $table->foreign('upload_id')->references('id')->on('twilio_phone_uploads')->onDelete('cascade');
            // Optionally add foreign key constraint for admin_id if you have an admins table
            // $table->foreign('admin_id')->references('id')->on('admins')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('twilio_phone_numbers');
    }
};
