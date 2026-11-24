
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // MySQL may retain the table after failing to add the original long index name.
        if (Schema::hasTable('restaurant_certifications')) {
            if (! Schema::hasIndex('restaurant_certifications', ['restaurant_id', 'certification_type_id'], 'unique')) {
                Schema::table('restaurant_certifications', function (Blueprint $table) {
                    $table->unique(['restaurant_id', 'certification_type_id'], 'rc_restaurant_type_unique');
                });
            }
            return;
        }

        Schema::create('restaurant_certifications', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('restaurant_id');
            $table->foreignId('certification_type_id');

            $table->text('file_path');
            $table->string('file_name')->nullable();

            $table->enum('status', [
                'not_submitted',
                'submitted',
                'under_review',
                'approved',
                'rejected',
                'changes_requested',
            ])->default('submitted');

            $table->timestamp('submitted_at')->nullable();
            $table->text('admin_notes')->nullable();

            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->timestamp('reviewed_at')->nullable();

            $table->timestamps();

            $table->foreign('restaurant_id')
                ->references('id')
                ->on('restaurants')
                ->cascadeOnDelete();

            $table->foreign('certification_type_id')
                ->references('id')
                ->on('certification_types')
                ->cascadeOnDelete();

            $table->foreign('reviewed_by')
                ->references('id')
                ->on('admins')
                ->nullOnDelete();

            $table->unique(['restaurant_id', 'certification_type_id'], 'rc_restaurant_type_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('restaurant_certifications');
    }
};
