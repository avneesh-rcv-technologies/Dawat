<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('twilio_phone_uploads', function (Blueprint $table) {
            $table->id();
            $table->string('filename');
            $table->unsignedBigInteger('uploaded_by');
            $table->integer('row_count')->default(0);
            $table->string('status')->default('uploaded');
            $table->json('data')->nullable();
            $table->text('error')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('twilio_phone_uploads');
    }
};
