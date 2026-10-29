<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('discounts', function (Blueprint $table) {
            // Check if admin_id column exists and remove it
            if (Schema::hasColumn('discounts', 'admin_id')) {
                $table->dropForeign(['admin_id']);
                $table->dropColumn('admin_id');
            }

            // Add polymorphic columns for created_by relationship
            if (!Schema::hasColumn('discounts', 'created_by_type')) {
                $table->string('created_by_type')->nullable()->after('description');
            }
            
            if (!Schema::hasColumn('discounts', 'created_by_id')) {
                $table->unsignedBigInteger('created_by_id')->nullable()->after('created_by_type');
            }

            // Add index for the morph relationship
            $table->index(['created_by_type', 'created_by_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('discounts', function (Blueprint $table) {
            // Drop the morph columns
            $table->dropIndex(['created_by_type', 'created_by_id']);
            $table->dropColumn(['created_by_type', 'created_by_id']);

            // Restore admin_id column
            $table->foreignId('admin_id')->nullable()->constrained('admins')->after('description');
        });
    }
};
