<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('restaurants', function (Blueprint $table) {
            $table->string('business_license')->nullable()->after('phone');
            $table->text('cuisine_type')->nullable()->after('food_type');
            $table->time('opening_time')->nullable()->after('cuisine_type');
            $table->time('closing_time')->nullable()->after('opening_time');
            $table->enum('approval_status', ['pending', 'approved', 'rejected'])->default('pending')->after('status');
            $table->text('rejection_reason')->nullable()->after('approval_status');
            $table->timestamp('approved_at')->nullable()->after('rejection_reason');
            $table->unsignedBigInteger('approved_by')->nullable()->after('approved_at');
        });
    }

    public function down(): void
    {
        Schema::table('restaurants', function (Blueprint $table) {
            $table->dropColumn([
                'business_license',
                'cuisine_type',
                'opening_time',
                'closing_time',
                'approval_status',
                'rejection_reason',
                'approved_at',
                'approved_by'
            ]);
        });
    }
};
