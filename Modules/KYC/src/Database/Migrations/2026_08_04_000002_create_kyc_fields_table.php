<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('kyc_fields', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('kyc_type_id');
            $table->string('field_name', 150);
            $table->string('field_key', 150);
            $table->enum('field_type', ['text', 'number', 'file', 'date', 'select']);
            $table->boolean('is_required')->default(1);
            $table->json('options')->nullable();
            $table->integer('order_index')->default(0);
            $table->timestamps();
            
            $table->foreign('kyc_type_id')
                ->references('id')
                ->on('kyc_types')
                ->onDelete('cascade');
                
            $table->unique(['kyc_type_id', 'field_key'], 'unique_field_key');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kyc_fields');
    }
};
