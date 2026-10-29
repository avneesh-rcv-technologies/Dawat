<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('compliance_records', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');        // admins.id
            $table->unsignedBigInteger('kyc_type_id');    // a compliance-type id
            $table->unsignedBigInteger('submission_id');  // latest kyc_submissions.id
            $table->date('valid_from')->nullable();
            $table->dateTime('expires_at')->nullable();
            $table->string('validity_period', 20)->nullable(); // snapshot of period used
            $table->enum('status', ['active', 'expiring_soon', 'expired'])->default('active');
            $table->dateTime('reminder_sent_at')->nullable();    // "expiring soon" sent
            $table->dateTime('expired_notified_at')->nullable(); // "expired" sent
            $table->timestamps();

            // one live record per restaurant owner per compliance type (renewal updates the same row)
            $table->unique(['user_id', 'kyc_type_id']);
            $table->index(['status', 'expires_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('compliance_records');
    }
};
