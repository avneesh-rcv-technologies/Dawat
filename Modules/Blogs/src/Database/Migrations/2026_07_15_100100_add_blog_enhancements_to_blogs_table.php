<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('blogs', function (Blueprint $table) {
            $table->foreignId('author_id')
                ->nullable()
                ->after('author_name')
                ->constrained('blog_authors')
                ->nullOnDelete();

            $table->longText('schema')->nullable()->after('meta_keywords');
            $table->timestamp('scheduled_at')->nullable()->after('status');
            $table->string('scheduled_timezone', 64)->nullable()->after('scheduled_at');
        });

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE blogs MODIFY COLUMN status ENUM('draft','published','archived','scheduled') NOT NULL DEFAULT 'draft'");
        } elseif ($driver === 'pgsql') {
            DB::statement("ALTER TABLE blogs DROP CONSTRAINT IF EXISTS blogs_status_check");
            DB::statement("ALTER TABLE blogs ADD CONSTRAINT blogs_status_check CHECK (status IN ('draft','published','archived','scheduled'))");
        }
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement("UPDATE blogs SET status = 'draft' WHERE status = 'scheduled'");
            DB::statement("ALTER TABLE blogs MODIFY COLUMN status ENUM('draft','published','archived') NOT NULL DEFAULT 'draft'");
        } elseif ($driver === 'pgsql') {
            DB::statement("UPDATE blogs SET status = 'draft' WHERE status = 'scheduled'");
            DB::statement("ALTER TABLE blogs DROP CONSTRAINT IF EXISTS blogs_status_check");
            DB::statement("ALTER TABLE blogs ADD CONSTRAINT blogs_status_check CHECK (status IN ('draft','published','archived'))");
        }

        Schema::table('blogs', function (Blueprint $table) {
            $table->dropConstrainedForeignId('author_id');
            $table->dropColumn(['schema', 'scheduled_at', 'scheduled_timezone']);
        });
    }
};
