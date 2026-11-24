<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->changeReviewerTable('admins');
    }

    public function down(): void
    {
        $this->changeReviewerTable('users');
    }

    private function changeReviewerTable(string $reviewerTable): void
    {
        if (DB::table('restaurant_certifications')
            ->whereNotNull('reviewed_by')
            ->whereNotIn('reviewed_by', DB::table($reviewerTable)->select('id'))
            ->exists()) {
            throw new RuntimeException("Resolve certification reviewer IDs missing from {$reviewerTable} before migrating.");
        }

        Schema::table('restaurant_certifications', function (Blueprint $table) {
            $table->dropForeign(['reviewed_by']);
        });

        Schema::table('restaurant_certifications', function (Blueprint $table) use ($reviewerTable) {
            $table->foreign('reviewed_by')
                ->references('id')
                ->on($reviewerTable)
                ->nullOnDelete();
        });
    }
};
