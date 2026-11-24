<?php

use Illuminate\Database\Migrations\Migration;
use Modules\Orders\Database\Seeders\OrderWorkflowStatusSeeder;

return new class extends Migration
{
    public function up(): void
    {
        // Install missing codes without overwriting existing status customizations.
        (new OrderWorkflowStatusSeeder())->run();
    }

    public function down(): void
    {
        // Keep reference data: existing orders may reference these status codes.
    }
};
