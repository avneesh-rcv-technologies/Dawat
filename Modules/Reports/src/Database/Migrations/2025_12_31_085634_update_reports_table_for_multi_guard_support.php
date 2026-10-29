<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            // Convert guard from string to JSON array for multi-guard support
            // First, migrate existing data
            DB::statement("UPDATE reports SET guard = JSON_ARRAY(guard) WHERE guard IS NOT NULL");
            
            // Change column type to JSON
            $table->json('guards')->nullable()->after('description');
        });
        
        // Copy data from guard to guards, then drop guard
        DB::statement("UPDATE reports SET guards = guard");
        
        Schema::table('reports', function (Blueprint $table) {
            $table->dropIndex(['guard']);
            $table->dropColumn('guard');
            // Note: JSON columns cannot be indexed directly in MySQL
        });
        
        // Update allowed_users structure to include model information
        // New format: [{"model": "App\\Models\\Admin", "id": 1}, ...]
        $reports = DB::table('reports')->whereNotNull('allowed_users')->get();
        
        foreach ($reports as $report) {
            $allowedUsers = json_decode($report->allowed_users, true);
            
            if (is_array($allowedUsers) && !empty($allowedUsers)) {
                $restructured = [];
                $guards = json_decode($report->guards, true) ?? ['admin'];
                $primaryGuard = $guards[0] ?? 'admin';
                
                // Get the model class for this guard
                $guardConfig = config("acl::guards.guards.{$primaryGuard}");
                $provider = $guardConfig['provider'] ?? $primaryGuard;
                $providerConfig = config("acl::guards.providers.{$provider}");
                $modelClass = $providerConfig['model'] ?? null;
                
                // If no model, fallback to table-based approach
                if (!$modelClass) {
                    $modelClass = 'Modules\\Acl\\Models\\' . ucfirst($provider);
                }
                
                foreach ($allowedUsers as $userId) {
                    if (is_numeric($userId) || is_string($userId)) {
                        $restructured[] = [
                            'model' => $modelClass,
                            'id' => (int)$userId,
                            'guard' => $primaryGuard
                        ];
                    }
                }
                
                DB::table('reports')
                    ->where('id', $report->id)
                    ->update(['allowed_users' => json_encode($restructured)]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            // Revert guards to single guard column
            $table->string('guard')->default('admin')->after('description');
        });
        
        // Take first guard from guards array
        DB::statement("UPDATE reports SET guard = JSON_UNQUOTE(JSON_EXTRACT(guards, '$[0]'))");
        
        Schema::table('reports', function (Blueprint $table) {
            $table->dropColumn('guards');
            $table->index('guard');
        });
        
        // Revert allowed_users to simple ID array
        $reports = DB::table('reports')->whereNotNull('allowed_users')->get();
        
        foreach ($reports as $report) {
            $allowedUsers = json_decode($report->allowed_users, true);
            
            if (is_array($allowedUsers) && !empty($allowedUsers)) {
                $simpleIds = array_map(function($user) {
                    return $user['id'] ?? $user;
                }, $allowedUsers);
                
                DB::table('reports')
                    ->where('id', $report->id)
                    ->update(['allowed_users' => json_encode($simpleIds)]);
            }
        }
    }
};
