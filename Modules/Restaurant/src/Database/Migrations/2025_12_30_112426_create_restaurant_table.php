<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('restaurants', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('admin_id')->nullable();

            // TAB 1
            $table->string('name', 150);
            $table->string('slug', 160)->unique();
            $table->string('phone', 20);

            // TAB 3
            $table->string('address_line1')->nullable();
            $table->string('address_line2')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('country')->nullable();
            $table->string('postal_code')->nullable();

            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();

            // TAB 4
            $table->enum('food_type', ['all', 'veg', 'non_veg', 'vegan'])->nullable();
            $table->boolean('is_takeaway')->default(false);
            $table->boolean('is_delivery')->default(false);
            $table->boolean('is_accepting_orders')->default(false);

            $table->enum('status', ['draft', 'active', 'inactive', 'closed'])
                ->default('draft');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('restaurants');
    }
};
