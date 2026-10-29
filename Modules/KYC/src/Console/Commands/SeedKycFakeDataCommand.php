<?php

namespace Modules\KYC\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Modules\KYC\Database\Seeders\KycFakeDataSeeder;

class SeedKycFakeDataCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'kyc:seed-fake-data {--fresh : Clear existing KYC data before seeding}';

    /**
     * The console command description.
     */
    protected $description = 'Seed fake KYC data with sample submissions and images';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if ($this->option('fresh')) {
            $this->info('Clearing existing KYC data...');
            $this->clearExistingData();
        }

        $this->info('Seeding fake KYC data...');
        
        $seeder = new KycFakeDataSeeder();
        $seeder->setCommand($this);
        $seeder->run();
        
        $this->info('✅ KYC fake data seeded successfully!');
        $this->newLine();
        
        $this->displaySummary();
        
        return Command::SUCCESS;
    }

    /**
     * Clear existing KYC data
     */
    protected function clearExistingData(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        DB::table('kyc_messages')->truncate();
        DB::table('kyc_files')->truncate();
        DB::table('kyc_submission_data')->truncate();
        DB::table('kyc_submissions')->truncate();
        DB::table('kyc_requests')->truncate();
        DB::table('kyc_fields')->truncate();
        DB::table('kyc_types')->truncate();
        
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        
        // Clear uploaded files
        Storage::disk('public')->deleteDirectory('kyc');
        
        $this->info('Existing KYC data cleared.');
    }

    /**
     * Display summary of seeded data
     */
    protected function displaySummary(): void
    {
        $kycTypesCount = DB::table('kyc_types')->count();
        $fieldsCount = DB::table('kyc_fields')->count();
        $submissionsCount = DB::table('kyc_submissions')->count();
        $filesCount = DB::table('kyc_files')->count();
        $messagesCount = DB::table('kyc_messages')->count();
        $requestsCount = DB::table('kyc_requests')->count();
        
        $this->table(
            ['Entity', 'Count'],
            [
                ['KYC Types', $kycTypesCount],
                ['KYC Fields', $fieldsCount],
                ['KYC Submissions', $submissionsCount],
                ['KYC Files', $filesCount],
                ['KYC Messages', $messagesCount],
                ['KYC Requests', $requestsCount],
            ]
        );
        
        $this->info('📊 Summary:');
        $this->line("• {$kycTypesCount} KYC types created with comprehensive fields");
        $this->line("• {$submissionsCount} fake submissions with realistic data");
        $this->line("• {$filesCount} sample document images uploaded");
        $this->line("• {$messagesCount} conversation messages between admin and users");
        $this->line("• {$requestsCount} KYC requests for different document types");
        
        $this->newLine();
        $this->info('🎯 KYC Types Created:');
        $kycTypes = DB::table('kyc_types')->pluck('name');
        foreach ($kycTypes as $type) {
            $this->line("  • {$type}");
        }
        
        $this->newLine();
        $this->info('📁 Sample files are stored in: storage/app/public/kyc/');
        $this->info('🌐 You can access them via: /storage/kyc/[user_id]/[submission_id]/[filename]');
    }
}