<?php

namespace Modules\Twilio\Jobs;

use Throwable;
use Illuminate\Bus\Queueable;
use Modules\Acl\Models\Admin;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Queue\SerializesModels;
use Modules\Twilio\Models\PhoneNumber;
use Illuminate\Queue\InteractsWithQueue;
use Modules\Twilio\Models\TwilioMessage;
use Modules\Twilio\Services\TwilioService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Modules\Activitylog\Exceptions\ApiExceptionHandler;
use Modules\Activitylog\Services\ActivityLoggerService;
use Modules\Notifications\Services\NotificationService;

class SendSmsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = 10;

    protected $phoneNumberId;
    protected $message;
    protected $messageId;

    public function __construct(int $phoneNumberId, string $message = null, ?int $messageId = null)
    {
        $this->phoneNumberId = $phoneNumberId;
        $this->message = $message;
        $this->messageId = $messageId;
    }

    public function handle()
    {
        $phoneNumber = PhoneNumber::find($this->phoneNumberId);
        if (!$phoneNumber) {
            ActivityLoggerService::log(
                'Twilio Phone Number Not Found',
                Auth::guard('admin')->user(),
                [
                    'user_id'    => Auth::guard('admin')->id(),
                    'email'      => Auth::guard('admin')->user()->email ?? null,
                    'errors'     => 'Phone number not found',
                    'attempt_ip' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                    'event'      => 'twilio_phone_number_not_found',
                    'severity'   => 'critical',
                ],
                'admin'
            );
            return;
        }

        // Mark phone number as processing for quick UI feedback
        $phoneNumber->update([
            'status' => 'processing',
            'message' => $this->message,
        ]);

        // Try to find the specific message record if a message id was passed, otherwise fall back to a queued message
        $queuedMessage = null;
        if ($this->messageId) {
            $queuedMessage = TwilioMessage::find($this->messageId);
        }

        if (!$queuedMessage) {
            $queuedMessage = TwilioMessage::where('phone_number_id', $phoneNumber->id)
                ->where('body', $this->message)
                ->whereIn('status', ['queued', 'processing'])
                ->latest()
                ->first();
        }

        // Perform the external network call outside of any DB transaction
        try {
            $twilio = app(TwilioService::class);
            ActivityLoggerService::log(
                'Twilio Message Send',
                Auth::guard('admin')->user(),
                [
                    'user_id'    => Auth::guard('admin')->id(),
                    'email'      => Auth::guard('admin')->user()->email ?? null,
                    'errors'     => '',
                    'attempt_ip' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                    'event'      => 'twilio_message_send',
                    'severity'   => 'critical',
                ],
                'admin'
            );
            $response = $twilio->sendMessage($phoneNumber->phone_number, $this->message);

            $sid = null;
            if (is_object($response) && isset($response->sid)) {
                $sid = $response->sid;
            } elseif (is_array($response) && isset($response['sid'])) {
                $sid = $response['sid'];
            }

            // Persist updates in a transaction and acquire row-level lock if we have an existing queued message
            DB::transaction(function () use ($phoneNumber, $queuedMessage, $sid) {
                if ($queuedMessage) {
                    // Lock the specific row to avoid races
                    $msg = TwilioMessage::where('id', $queuedMessage->id)->lockForUpdate()->first();
                    if ($msg) {
                        $msg->update([
                            'delivery_sid' => $sid,
                            'status' => 'sent',
                            'sent_at' => now(),
                            'retry_count' => 0,
                        ]);
                    }
                } else {
                    TwilioMessage::create([
                        'phone_number_id' => $phoneNumber->id,
                        'admin_id' => $phoneNumber->admin_id,
                        'to' => $phoneNumber->phone_number,
                        'body' => $this->message,
                        'delivery_sid' => $sid,
                        'status' => 'sent',
                        'sent_at' => now(),
                        'retry_count' => 0,
                    ]);
                }



                ActivityLoggerService::log(
                    'Twilio Message Sent',
                    Auth::guard('admin')->user(),
                    [
                        'user_id'    => Auth::guard('admin')->id(),
                        'email'      => Auth::guard('admin')->user()->email ?? null,
                        'errors'     => '',
                        'attempt_ip' => request()->ip(),
                        'user_agent' => request()->userAgent(),
                        'event'      => 'twilio_message_sent',
                        'severity'   => 'info',
                    ],
                    'admin'
                );

                // Update phone number summary fields atomically
                $phoneNumber->increment('send_count');
                $phoneNumber->update([
                    'delivery_sid' => $sid,
                    'status' => 'sent',
                    'sent_at' => now(),
                ]);

                $notificationService = app(NotificationService::class);
                $admin = Admin::find($phoneNumber->admin_id);
                $notificationService->trigger(
                    'Twilio',
                    'MessageSend',
                    $admin->email,
                    [
                        'data' => $phoneNumber,
                        'user' => $admin,
                    ]
                );
            });

            return;
        } catch (Throwable $e) {
            // On failure, record it on the message history and mark phone number failed
            try {
                DB::transaction(function () use ($phoneNumber, $queuedMessage, $e) {
                    if ($queuedMessage) {
                        $msg = TwilioMessage::where('id', $queuedMessage->id)->lockForUpdate()->first();
                        if ($msg) {
                            $msg->increment('retry_count');
                            $msg->update([
                                'status' => 'failed',
                                'error' => $e->getMessage(),
                            ]);
                        }
                    } else {
                        TwilioMessage::create([
                            'phone_number_id' => $phoneNumber->id,
                            'admin_id' => $phoneNumber->admin_id,
                            'to' => $phoneNumber->phone_number,
                            'body' => $this->message,
                            'status' => 'failed',
                            'error' => $e->getMessage(),
                            'retry_count' => 1,
                        ]);
                    }

                    ActivityLoggerService::log(
                        'Twilio Message Failed',
                        Auth::guard('admin')->user(),
                        [
                            'user_id'    => Auth::guard('admin')->id(),
                            'email'      => Auth::guard('admin')->user()->email ?? null,
                            'errors'     => $e->getMessage(),
                            'attempt_ip' => request()->ip(),
                            'user_agent' => request()->userAgent(),
                            'event'      => 'twilio_message_failed',
                            'severity'   => 'critical',
                        ],
                        'admin'
                    );
                    $phoneNumber->update(['status' => 'failed']);

                    $notificationService = app(NotificationService::class);
                    $admin = Admin::find($phoneNumber->admin_id);
                    $notificationService->trigger(
                        'Twilio',
                        'MessageSendFailed',
                        $admin->email,
                        [
                            'data' => $phoneNumber,
                            'user' => $admin,
                        ]
                    );
                });
            } catch (Throwable $inner) {
                ApiExceptionHandler::handle($inner);
            }
            // rethrow to allow queue retry behaviour
            throw $e;
        }
    }
}
