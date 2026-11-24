
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dietary_labels', function (Blueprint $table) {
            $table->id();

            $table->string('name', 100);
            $table->string('code', 10)->nullabe();
            $table->string('slug', 100)->unique();
            $table->string('icon', 255)->nullable();
            $table->text('description')->nullable();
            $table->string('backend_value', 50)->unique();

            $table->boolean('is_active')->default(true);
            $table->boolean('is_certification_required')->default(false);
            $table->integer('display_order')->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dietary_labels');
    }
};
