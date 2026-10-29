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
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique()->comment('Processor identifier (stripe, paypal)');
            $table->string('class_name')->comment('Payment processor class');
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->string('template')->nullable()->comment('Processor configuration');
            $table->string('processor')->nullable()->comment('Processor configuration');
            $table->timestamps();
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_methods');
    }
};
