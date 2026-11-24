<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('blog_authors', function (Blueprint $table) {
            if (! Schema::hasColumn('blog_authors', 'designation')) {
                $table->string('designation')->nullable()->after('name');
            }
        });
    }

    public function down(): void
    {
        Schema::table('blog_authors', function (Blueprint $table) {
            if (Schema::hasColumn('blog_authors', 'designation')) {
                $table->dropColumn('designation');
            }
        });
    }
};
