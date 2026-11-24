
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('food_items', function (Blueprint $table) {
            $table->json('dietary_label_ids')->nullable();
            $table->dropColumn('dietary_label_id');
        });
    }

    public function down(): void
    {
        Schema::table('food_items', function (Blueprint $table) {
            $table->string('dietary_label_id', 36)->nullable();
            $table->dropColumn('dietary_label_ids');
        });
    }
};
