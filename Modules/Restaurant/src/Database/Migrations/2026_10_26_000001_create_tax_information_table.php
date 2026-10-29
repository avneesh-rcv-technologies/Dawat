<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tax_information', function (Blueprint $table) {
            $table->id();
            $table->morphs('taxable');
            $table->enum('tax_type', ['individual', 'business']);

            $table->string('full_name')->nullable();
            $table->text('ssn')->nullable();

            $table->string('company_legal_name')->nullable();
            $table->string('doing_business_as')->nullable();
            $table->text('ein')->nullable();

            $table->string('address');
            $table->integer('city_id');
            $table->integer('state_id');
            $table->string('zip_code', 20);
            $table->integer('country_id');

            $table->boolean('same_mailing_address')->default(true);
            $table->string('mailing_address')->nullable();
            $table->string('mailing_city', 100)->nullable();
            $table->string('mailing_state', 100)->nullable();
            $table->string('mailing_zip_code', 20)->nullable();

            $table->boolean('receive_1099_electronically')->default(false);
            $table->boolean('backup_tax_withholding')->default(false);
            $table->boolean('tax_certification')->default(false);
            $table->timestamp('tax_certified_at')->nullable();

            $table->timestamps();
            $table->unique(['taxable_id', 'taxable_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tax_information');
    }
};
