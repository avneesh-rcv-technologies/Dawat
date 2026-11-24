
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('dietary_labels')) {
            return;
        }

        Schema::table('dietary_labels', function (Blueprint $table) {

            if (!Schema::hasColumn('dietary_labels', 'name')) {
                $table->string('name', 100)->nullable();
            }

            if (!Schema::hasColumn('dietary_labels', 'code')) {
                $table->string('code', 10)->nullable();
            }

            if (!Schema::hasColumn('dietary_labels', 'slug')) {
                $table->string('slug', 100)->nullable()->unique();
            }

            if (!Schema::hasColumn('dietary_labels', 'icon')) {
                $table->string('icon', 255)->nullable();
            }

            if (!Schema::hasColumn('dietary_labels', 'description')) {
                $table->text('description')->nullable();
            }

            if (!Schema::hasColumn('dietary_labels', 'backend_value')) {
                $table->string('backend_value', 50)->nullable()->unique();
            }

            if (!Schema::hasColumn('dietary_labels', 'is_active')) {
                $table->boolean('is_active')->default(true);
            }

            if (!Schema::hasColumn('dietary_labels', 'is_certification_required')) {
                $table->boolean('is_certification_required')->default(false);
            }

            if (!Schema::hasColumn('dietary_labels', 'display_order')) {
                $table->integer('display_order')->default(0);
            }

            if (!Schema::hasColumn('dietary_labels', 'created_at')) {
                $table->timestamp('created_at')->nullable();
            }

            if (!Schema::hasColumn('dietary_labels', 'updated_at')) {
                $table->timestamp('updated_at')->nullable();
            }

        });
    }

    public function down(): void
    {
        // Keep existing columns and data safe.
    }
};
