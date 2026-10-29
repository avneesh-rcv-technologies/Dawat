<?php

namespace Modules\Twilio\Services;

use Illuminate\Support\Facades\Log;
use Modules\Twilio\Jobs\SendSmsJob;
use Illuminate\Support\Facades\Auth;
use Modules\Twilio\Models\PhoneNumber;
use Modules\Twilio\Models\TwilioMessage;
use Modules\Twilio\Services\MessagingAutomationService;
use Modules\Activitylog\Exceptions\ApiExceptionHandler;
use Modules\Activitylog\Services\ActivityLoggerService;

class SmsService {
    /**
     * Queue an SMS send for a PhoneNumber model.
     *
     * @param PhoneNumber $phoneNumber
     * @param string $message
     * @return void
     */
    public function sendToNumber(PhoneNumber $phoneNumber, string $message = null): void
    {
        // Ensure model is fresh
        $phoneNumber->refresh();

        // Update status to queued
        $phoneNumber->update([
            'message' => $message,
            'status' => 'queued',
        ]);

        // Create a TwilioMessage record immediately so there's a DB record even if the worker is down
        $messageRecord = TwilioMessage::create([
            'phone_number_id' => $phoneNumber->id,
            'admin_id' => $phoneNumber->admin_id,
            'to' => $phoneNumber->phone_number,
            'body' => $message,
            'status' => 'queued',
            'retry_count' => 0,
        ]);

        $phoneNumber->retry_count++;
        $phoneNumber->save();

        ActivityLoggerService::log(
            'Twilio Message Added In Queue',
            Auth::guard('admin')->user(),
            [
                'user_id'    => Auth::guard('admin')->id(),
                'email'      => Auth::guard('admin')->user()->email ?? null,
                'errors'     => '',
                'attempt_ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'event'      => 'message_added_in_queue',
                'severity'   => 'critical',
            ],
            'admin'
        );
        // Dispatch job to send the SMS and pass the message id so the job can update the same record
        SendSmsJob::dispatch($phoneNumber->id, $message, $messageRecord->id);
    }

    /**
     * Handle inbound delivery/status callbacks from Twilio or inbound replies.
     *
     * @param array $payload
     * @return bool
     */
    public function handleInbound(array $payload): bool
    {
        try {
            // Twilio uses MessageSid for delivery callbacks and SmsSid for inbound too
            $sid = $payload['MessageSid'] ?? $payload['SmsSid'] ?? ($payload['MessageSid'] ?? null);
            $status = $payload['MessageStatus'] ?? $payload['SmsStatus'] ?? null;
            $from = $payload['From'] ?? null;
            $body = $payload['Body'] ?? null;

            ActivityLoggerService::log(
                'Webhook:: Twilio Inbound',
                Auth::guard('admin')->user(),
                [
                    'user_id'    => Auth::guard('admin')->id(),
                    'email'      => Auth::guard('admin')->user()->email ?? null,
                    'errors'     => '',
                    'attempt_ip' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                    'event'      => 'twilio_inbound',
                    'severity'   => 'info',
                ],
                'admin'
            );

            if (!$sid) {
                return false;
            }

            // Try to find a matching message first
            $message = null;
            if ($sid) {
                $message = \Modules\Twilio\Models\TwilioMessage::where('delivery_sid', $sid)->first();
            }

            $phoneNumber = null;
            if ($message) {
                $phoneNumber = $message->phoneNumber;
            }

            if (!$phoneNumber && $from) {
                // Try to match by phone number if sid not present
                $phoneNumber = PhoneNumber::where('phone_number', $from)->latest()->first();
            }

            if (!$phoneNumber) {
                ActivityLoggerService::log(
                    'Webhook:: Twilio Phone Number Not Found',
                    Auth::guard('admin')->user(),
                    [
                        'user_id'    => Auth::guard('admin')->id(),
                        'email'      => Auth::guard('admin')->user()->email ?? null,
                        'errors'     => 'Phone number not found',
                        'attempt_ip' => request()->ip(),
                        'user_agent' => request()->userAgent(),
                        'event'      => 'twilio_message_not_found',
                        'severity'   => 'critical',
                    ],
                    'admin'
                );
                return false;
            }

            // Update response and status
            $update = [];
            if ($body) {
                $update['response'] = $body;
                $update['response_at'] = now();
            }
            if ($status) {
                $update['status'] = $status;
            }

            // Update message record if present
            if ($message) {
                if (isset($update['response'])) {
                    $message->update(['response' => $update['response'], 'response_at' => $update['response_at']]);
                }
                if (isset($update['status'])) {
                    $message->update(['status' => $update['status']]);
                }
            }

            // Also update all messages for this phone number so status/response propagate
            try {
                if (!empty($phoneNumber->id)) {
                    // Update response for messages that don't yet have a response
                    if (isset($update['response'])) {
                        \Modules\Twilio\Models\TwilioMessage::where('phone_number_id', $phoneNumber->id)
                            ->whereNull('response')
                            ->update(['response' => $update['response'], 'response_at' => $update['response_at']]);
                    }

                    // Update status for messages where the status differs
                    if (isset($update['status'])) {
                        \Modules\Twilio\Models\TwilioMessage::where('phone_number_id', $phoneNumber->id)
                            ->where('status', '!=', $update['status'])
                            ->update(['status' => $update['status']]);
                    }
                }
            } catch (\Exception $e) {
                ApiExceptionHandler::handle($e);
            }

            // Finally update the phone number record
            $phoneNumber->update($update);

            // Process automation rules for inbound messages with body content
            if ($body && $phoneNumber) {
                $this->processAutomation($phoneNumber, $body, $message);
            }

            return true;
        } catch (\Exception $e) {
            ApiExceptionHandler::handle($e);
            Log::error('SmsService inbound handling failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Process automation rules for inbound messages.
     *
     * @param PhoneNumber $phoneNumber
     * @param string $body
     * @param TwilioMessage|null $message
     * @return void
     */
    protected function processAutomation(PhoneNumber $phoneNumber, string $body, ?TwilioMessage $message = null): void
    {
        try {
            $automationService = app(MessagingAutomationService::class);
            $result = $automationService->processInboundMessage($phoneNumber, $message);

            if ($result) {
                ActivityLoggerService::log(
                    'Messaging Automation Processed',
                    Auth::guard('admin')->user(),
                    [
                        'user_id' => Auth::guard('admin')->id(),
                        'email' => Auth::guard('admin')->user()->email ?? null,
                        'phone_number_id' => $phoneNumber->id,
                        'phone_number' => $phoneNumber->phone_number,
                        'automation_action' => $result['action'],
                        'message' => $body,
                        'event' => 'automation_processed',
                        'severity' => 'info',
                    ],
                    'admin'
                );
            } else {
                // Log for manual review if no automation rules matched
                $automationService->logForManualReview($phoneNumber, $message);
            }
        } catch (\Exception $e) {
            Log::error('Automation processing failed: ' . $e->getMessage());
        }
    }
}
