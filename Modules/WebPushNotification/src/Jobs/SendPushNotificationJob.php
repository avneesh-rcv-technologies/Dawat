<?php

namespace Modules\WebPushNotification\Jobs;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Modules\Notifications\Models\NotificationLog;
use Modules\Activitylog\Exceptions\ApiExceptionHandler;
use Modules\WebPushNotification\Services\FirebaseNotificationService;

class SendPushNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 15; // Prevent long-running FCM calls

    protected string $token;
    protected string $device;
    protected string $title;
    protected string $body;
    protected array $data;
    protected ?int $logId;

    public function __construct(
        string $token,
        string $device,
        string $title,
        string $body,
        array $data = [],
        ?int $logId = null
    ) {
        $this->token   = $token;
        $this->device  = $device;
        $this->title   = $title;
        $this->body    = $body;
        $this->data    = $data;
        $this->logId   = $logId;
    }

    public function handle()
    {
        try {
            $service = new FirebaseNotificationService();
            $response = $service->sendNotification(
                $this->token,
                $this->device,
                $this->title,
                $this->body,
                $this->data
            );
            Log::info("Push notification sent", [
                'log_id' => $this->logId,
                'response'  => $response,
            ]);

            if ($this->logId) {
                NotificationLog::where('id', $this->logId)->update([
                    'status' => 'sent', 
                ]);
            }

        } catch (Exception $e) {

            ApiExceptionHandler::handle($e);
            Log::error("Push notification failed", [
                'log_id' => $this->logId,
                'error'  => $e->getMessage(),
            ]);

            if ($this->logId) {
                NotificationLog::where('id', $this->logId)->update([
                    'status'        => 'failed',
                    'error_message' => $e->getMessage(),
                ]);
            }

            throw $e; // Allow Laravel to retry
        }
    }

    /**
     * Handle failed job (after retries).
     */
    public function failed(Exception $exception)
    {
        if ($this->logId) {
            NotificationLog::where('id', $this->logId)->update([
                'status'        => 'failed_permanently',
                'error_message' => $exception->getMessage(),
            ]);
        }
    }
}
