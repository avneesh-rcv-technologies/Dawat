<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ratings', function (Blueprint $table) {
            $table->foreignId('order_id')->nullable()->after('user_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('order_item_id')->nullable()->after('order_id')->constrained('order_items')->cascadeOnDelete();
            $table->string('order_subject_key', 80)->nullable();
            $table->string('admin_status', 16)->default('pending')->after('is_approved');
            $table->foreignId('admin_approved_by')->nullable()->constrained('admins')->nullOnDelete();
            $table->timestamp('admin_approved_at')->nullable();
            $table->string('restaurant_status', 16)->default('pending');
            $table->foreignId('restaurant_approved_by')->nullable()->constrained('admins')->nullOnDelete();
            $table->timestamp('restaurant_approved_at')->nullable();
            $table->text('restaurant_reply')->nullable();
            $table->timestamp('restaurant_replied_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->index(['admin_status', 'ratable_type', 'status'], 'ratings_admin_workflow_idx');
            $table->index(['restaurant_status', 'ratable_type', 'status'], 'ratings_restaurant_workflow_idx');
            $table->unique(['order_id', 'order_subject_key'], 'ratings_order_subject_unique');
        });

        // Preserve legacy visibility and approval state for existing records.
        DB::table('ratings')->where('is_approved', true)->update(['admin_status' => 'approved']);
    }

    public function down(): void
    {
        Schema::table('ratings', function (Blueprint $table) {
            $table->dropUnique('ratings_order_subject_unique');
            $table->dropIndex('ratings_admin_workflow_idx');
            $table->dropIndex('ratings_restaurant_workflow_idx');
            $table->dropConstrainedForeignId('order_item_id');
            $table->dropConstrainedForeignId('order_id');
            $table->dropConstrainedForeignId('restaurant_approved_by');
            $table->dropConstrainedForeignId('admin_approved_by');
            $table->dropColumn('order_subject_key');
            $table->dropColumn([
                'admin_status', 'admin_approved_at',
                'restaurant_status', 'restaurant_approved_at',
                'restaurant_reply', 'restaurant_replied_at', 'rejection_reason',
            ]);
        });
    }
};
