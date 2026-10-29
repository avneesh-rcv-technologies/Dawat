<?php

namespace Modules\Twilio\Jobs;

use Throwable;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Modules\Twilio\Services\TwilioService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Modules\Notifications\Models\NotificationLog;

class SendNotificationSmsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = 10;

    protected $recipients;
    protected $message;
    protected $logId;
    protected $config;

    /**
     * Create a new job instance.
     *
     * @param array $recipients Array of phone numbers
     * @param string $message SMS message content
     * @param int $logId Notification log ID
     * @param array $config Twilio configuration
     */
    public function __construct(array $recipients, string $message, int $logId, array $config)
    {
        $this->recipients = $recipients;
        $this->message = $message;
        $this->logId = $logId;
        $this->config = $config;
    }

    public function handle()
    {
        $log = NotificationLog::find($this->logId);
        
        if (!$log) {
            Log::error('Notification log not found', ['log_id' => $this->logId]);
            return;
        }

        try {
            $twilio = app(TwilioService::class);
            $results = [];
            
            foreach ($this->recipients as $recipient) {
                try {
                    $response = $twilio->sendMessage($recipient, $this->message);
                    
                    $sid = null;
                    if (is_object($response) && isset($response->sid)) {
                        $sid = $response->sid;
                    } elseif (is_array($response) && isset($response['sid'])) {
                        $sid = $response['sid'];
                    }
                    
                    $results[] = [
                        'recipient' => $recipient,
                        'status' => 'sent',
                        'sid' => $sid
                    ];
                    
                    Log::info('SMS sent successfully', [
                        'recipient' => $recipient,
                        'sid' => $sid,
                        'log_id' => $this->logId
                    ]);
                } catch (Throwable $e) {
                    $results[] = [
                        'recipient' => $recipient,
                        'status' => 'failed',
                        'error' => $e->getMessage()
                    ];
                    
                    Log::error('Failed to send SMS to recipient', [
                        'recipient' => $recipient,
                        'error' => $e->getMessage(),
                        'log_id' => $this->logId
                    ]);
                }
            }
            
            // Update notification log
            $allSent = collect($results)->every(fn($r) => $r['status'] === 'sent');
            
            $log->update([
                'status' => $allSent ? 'sent' : 'failed',
                'sent_at' => now(),
                'error_message' => $allSent ? null : json_encode($results)
            ]);
            
        } catch (Throwable $e) {
            Log::error('Failed to send notification SMS', [
                'error' => $e->getMessage(),
                'log_id' => $this->logId
            ]);
            
            $log->update([
                'status' => 'failed',
                'error_message' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }
}
