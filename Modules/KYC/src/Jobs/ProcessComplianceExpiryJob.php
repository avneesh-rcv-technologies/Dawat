<?php

namespace Modules\KYC\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Modules\KYC\Services\ComplianceService;

class ProcessComplianceExpiryJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(ComplianceService $service): void
    {
        $summary = $service->processExpiries();
        Log::info('Compliance expiry processed', $summary);
    }
}
