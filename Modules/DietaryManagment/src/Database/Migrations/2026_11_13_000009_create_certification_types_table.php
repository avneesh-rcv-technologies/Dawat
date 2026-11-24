
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('certification_types', function (Blueprint $table) {
            $table->id();

            $table->string('name', 150)->unique();

            $table->foreignId('dietary_label_id')
                ->nullable()
                ->unique()
                ->constrained('dietary_labels')
                ->nullOnDelete();

            $table->boolean('is_required_for_orders')->default(false);
            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('certification_types');
    }
};
