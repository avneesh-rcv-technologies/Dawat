<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Records the restaurant's agreement to the platform commission during the banking step,
     * including the percentage that was in effect when they agreed.
     */
    public function up(): void
    {
        Schema::table('restaurants', function (Blueprint $table) {
            if (!Schema::hasColumn('restaurants', 'platform_commission_accepted')) {
                $table->boolean('platform_commission_accepted')->default(false)->after('tax_type');
            }
            if (!Schema::hasColumn('restaurants', 'platform_commission_percentage')) {
                $table->decimal('platform_commission_percentage', 5, 2)->nullable()->after('platform_commission_accepted');
            }
            if (!Schema::hasColumn('restaurants', 'platform_commission_accepted_at')) {
                $table->timestamp('platform_commission_accepted_at')->nullable()->after('platform_commission_percentage');
            }
        });
    }

    public function down(): void
    {
        Schema::table('restaurants', function (Blueprint $table) {
            $table->dropColumn(['platform_commission_accepted', 'platform_commission_percentage', 'platform_commission_accepted_at']);
        });
    }
};
