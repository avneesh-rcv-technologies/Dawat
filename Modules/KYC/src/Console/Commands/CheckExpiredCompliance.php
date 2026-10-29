<?php

namespace Modules\KYC\Console\Commands;

use Illuminate\Console\Command;
use Modules\KYC\Jobs\ProcessComplianceExpiryJob;
use Modules\KYC\Services\ComplianceService;

class CheckExpiredCompliance extends Command
{
    protected $signature = 'compliance:check-expired {--sync : run inline instead of queue}';
    protected $description = 'Mark expired/expiring compliance records and notify restaurant owner + admin';

    public function handle(): int
    {
        if ($this->option('sync')) {
            $summary = app(ComplianceService::class)->processExpiries();
            $this->info("Done inline: {$summary['expired']} expired, {$summary['expiring_soon']} expiring soon.");
            return self::SUCCESS;
        }

        ProcessComplianceExpiryJob::dispatch();
        $this->info('Compliance expiry job dispatched.');
        return self::SUCCESS;
    }
}
