<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('restaurants', function (Blueprint $table) {
            $table->enum('tax_type', ['individual', 'business'])->default('individual');
        });
      
    }

    public function down(): void
    {
        Schema::table('restaurants', fn (Blueprint $table) => $table->dropColumn('tax_type'));
    }
};
