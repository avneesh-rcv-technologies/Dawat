<?php

namespace Modules\EmailNotification\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Modules\EmailNotification\Mail\NotificationEmail;

class TestJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 30;

    public function __construct(
        public string $email,
        public array $config = []
    ) {}

    public function handle()
    {        
        Log::info('TestMailJob started processing', ['email' => $this->email]);

        try {
            Mail::to($this->email)->send(new NotificationEmail(
                'Test Email Subject',
                '<h1>This is a test email</h1><p>Sent via queue at ' . now() . '</p>',
                $this->config
            ));

            Log::info('TestMailJob completed successfully');
        } catch (\Exception $e) {
            Log::error('TestMailJob failed: ' . $e->getMessage());
            throw $e;
        }
    }

    public function failed(\Throwable $exception)
    {
        Log::error('TestMailJob failed permanently', [
            'error' => $exception->getMessage(),
            'email' => $this->email
        ]);
    }
}
