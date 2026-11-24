<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Existing step 4 refers to KYC. The new optional step takes that position.
        DB::table('restaurants')->where('onboarding_step', 4)->update(['onboarding_step' => 5]);
    }

    public function down(): void
    {
        DB::table('restaurants')->where('onboarding_step', 5)->update(['onboarding_step' => 4]);
    }
};
