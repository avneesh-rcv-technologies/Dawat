<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('seo_pages', function (Blueprint $table) {
            if (! Schema::hasColumn('seo_pages', 'twitter_image')) {
                $table->string('twitter_image')->nullable()->after('og_image');
            }

            if (! Schema::hasColumn('seo_pages', 'schema')) {
                $table->json('schema')->nullable()->after('twitter_image');
            }
        });
    }

    public function down(): void
    {
        Schema::table('seo_pages', function (Blueprint $table) {
            if (Schema::hasColumn('seo_pages', 'schema')) {
                $table->dropColumn('schema');
            }

            if (Schema::hasColumn('seo_pages', 'twitter_image')) {
                $table->dropColumn('twitter_image');
            }
        });
    }
};
