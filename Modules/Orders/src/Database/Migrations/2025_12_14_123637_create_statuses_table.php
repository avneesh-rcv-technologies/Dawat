<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('statuses', function (Blueprint $table) {
            $table->id();
            $table->char('type_code', 1)->comment('O = Order, S = Shipping');
            $table->char('status_code', 1)->comment('P = Pending/Picked, C = Completed');
            $table->string('name');
            $table->string('color')->nullable();
            $table->boolean('new')->default(0)->comment('1 = New status, 0 = Not new');
            $table->timestamps();
            
            // Add unique constraint for type_code and status_code combination
            $table->unique(['type_code', 'status_code']);
            
            // Add index on status_code for foreign key reference
            $table->index('status_code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('statuses');
    }
};
