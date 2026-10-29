<?php

namespace Modules\Twilio\Services;

use Modules\Twilio\Models\MessagingAutomation;
use Modules\Twilio\Models\PhoneNumber;
use Modules\Twilio\Models\TwilioMessage;
use Modules\ChatGpt\Services\ChatGptService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class MessagingAutomationService
{
    protected ChatGptService $chatGptService;

    public function __construct(ChatGptService $chatGptService)
    {
        $this->chatGptService = $chatGptService;
    }

    /**
     * Process an inbound message for automation rules.
     *
     * @param PhoneNumber $phoneNumber
     * @param TwilioMessage $message
     * @return array|null
     */
    public function processInboundMessage(PhoneNumber $phoneNumber, TwilioMessage $message): ?array
    {
        $inboundMessage = $message->response;

        if (empty($inboundMessage)) {
            return null;
        }

        // Check for STOP keyword first (highest priority)
        if ($this->handleStopKeyword($inboundMessage, $phoneNumber)) {
            return [
                'action' => 'stop',
                'message' => 'STOP command processed - user opted out'
            ];
        }

        // Find matching automation rules
        $automationRules = $this->findMatchingRules($inboundMessage);

        if ($automationRules->isEmpty()) {
            // No automation rules match, check if we should use ChatGPT for general responses
            return $this->handleGeneralChatGptResponse($inboundMessage, $phoneNumber, $message);
        }

        // Process the first matching rule (highest priority)
        $rule = $automationRules->first();

        return $this->processAutomationRule($rule, $inboundMessage, $phoneNumber, $message);
    }

    /**
     * Handle STOP keyword for opt-out.
     *
     * @param string $message
     * @param PhoneNumber $phoneNumber
     * @return bool
     */
    protected function handleStopKeyword(string $message, PhoneNumber $phoneNumber): bool
    {
        $stopKeywords = ['STOP', 'STOPALL', 'UNSUBSCRIBE', 'CANCEL', 'END', 'QUIT'];

        $messageUpper = strtoupper(trim($message));

        foreach ($stopKeywords as $keyword) {
            if ($messageUpper === $keyword) {
                // Mark phone number as opted out
                $phoneNumber->update([
                    'status' => 'opted_out',
                    'response_at' => now(),
                    'response' => 'STOP - User opted out'
                ]);

                // Update all pending messages for this number
                TwilioMessage::where('phone_number_id', $phoneNumber->id)
                    ->whereIn('status', ['queued', 'pending'])
                    ->update([
                        'status' => 'cancelled',
                        'error' => 'User opted out with STOP command'
                    ]);

                Log::info('User opted out via STOP command', [
                    'phone_number_id' => $phoneNumber->id,
                    'phone' => $phoneNumber->phone_number,
                    'message' => $message
                ]);

                return true;
            }
        }

        return false;
    }

    /**
     * Find automation rules that match the inbound message.
     *
     * @param string $message
     * @return \Illuminate\Database\Eloquent\Collection
     */
    protected function findMatchingRules(string $message)
    {
        return MessagingAutomation::where('is_active', true)
            ->orderBy('priority', 'desc')
            ->orderBy('created_at', 'asc')
            ->get()
            ->filter(function ($rule) use ($message) {
                return $rule->matches($message);
            });
    }

    /**
     * Process a matching automation rule.
     *
     * @param MessagingAutomation $rule
     * @param string $inboundMessage
     * @param PhoneNumber $phoneNumber
     * @param TwilioMessage $message
     * @return array
     */
    protected function processAutomationRule(MessagingAutomation $rule, string $inboundMessage, PhoneNumber $phoneNumber, TwilioMessage $message): array
    {
        if ($rule->use_chatgpt) {
            return $this->processChatGptRule($rule, $inboundMessage, $phoneNumber, $message);
        }

        return $this->processTextRule($rule, $inboundMessage, $phoneNumber, $message);
    }

    /**
     * Process a text-based automation rule.
     *
     * @param MessagingAutomation $rule
     * @param string $inboundMessage
     * @param PhoneNumber $phoneNumber
     * @param TwilioMessage $message
     * @return array
     */
    protected function processTextRule(MessagingAutomation $rule, string $inboundMessage, PhoneNumber $phoneNumber, TwilioMessage $message): array
    {
        $response = $rule->getResponse($inboundMessage);

        // Queue the auto-response
        $this->sendAutoResponse($phoneNumber, $response, $message);

        return [
            'action' => 'auto_response',
            'rule_id' => $rule->id,
            'response' => $response,
            'message' => 'Auto-response sent based on keyword rule'
        ];
    }

    /**
     * Process a ChatGPT-based automation rule.
     *
     * @param MessagingAutomation $rule
     * @param string $inboundMessage
     * @param PhoneNumber $phoneNumber
     * @param TwilioMessage $message
     * @return array
     */
    protected function processChatGptRule(MessagingAutomation $rule, string $inboundMessage, PhoneNumber $phoneNumber, TwilioMessage $message): array
    {
        $customPrompt = $rule->chatgpt_prompt ?: "Respond to this message: '{$inboundMessage}'";

        $response = $this->chatGptService->generateSmsResponse($inboundMessage, $customPrompt, [
            'automation_rule' => $rule->keyword,
            'phone_number' => $phoneNumber->phone_number
        ]);

        if ($response) {
            // Queue the auto-response
            $this->sendAutoResponse($phoneNumber, $response, $message);

            return [
                'action' => 'chatgpt_response',
                'rule_id' => $rule->id,
                'response' => $response,
                'message' => 'ChatGPT auto-response sent based on keyword rule'
            ];
        }

        return [
            'action' => 'chatgpt_failed',
            'rule_id' => $rule->id,
            'message' => 'ChatGPT response generation failed'
        ];
    }

    /**
     * Handle general ChatGPT response for unmatched messages.
     *
     * @param string $inboundMessage
     * @param PhoneNumber $phoneNumber
     * @param TwilioMessage $message
     * @return array|null
     */
    protected function handleGeneralChatGptResponse(string $inboundMessage, PhoneNumber $phoneNumber, TwilioMessage $message): ?array
    {
        // Check if general ChatGPT responses are enabled in config
        if (!fn_get_setting('ai.api_key')) {
            return null;
        }

        $response = $this->chatGptService->generateSmsResponse(
            $inboundMessage,
            "Please provide a helpful response to this SMS inquiry: '{$inboundMessage}'"
        );

        if ($response) {
            $this->sendAutoResponse($phoneNumber, $response, $message);

            return [
                'action' => 'general_chatgpt',
                'response' => $response,
                'message' => 'General ChatGPT response sent'
            ];
        }

        return [
            'action' => 'general_chatgpt_failed',
            'message' => 'General ChatGPT response generation failed'
        ];
    }

    /**
     * Send an auto-response SMS.
     *
     * @param PhoneNumber $phoneNumber
     * @param string $response
     * @param TwilioMessage $originalMessage
     * @return void
     */
    protected function sendAutoResponse(PhoneNumber $phoneNumber, string $response, TwilioMessage $originalMessage): void
    {
        try {
            // Create a new message record for the auto-response
            $autoResponseMessage = TwilioMessage::create([
                'phone_number_id' => $phoneNumber->id,
                'admin_id' => $phoneNumber->admin_id ?: Auth::guard('admin')->id(),
                'to' => $phoneNumber->phone_number,
                'body' => $response,
                'status' => 'queued',
                'retry_count' => 0,
                'is_auto_response' => true,
                'original_message_id' => $originalMessage->id,
            ]);

            // Update the original message to indicate it received an auto-response
            $originalMessage->update([
                'has_auto_response' => true,
                'auto_response_at' => now()
            ]);

            Log::info('Auto-response queued', [
                'phone_number_id' => $phoneNumber->id,
                'original_message_id' => $originalMessage->id,
                'auto_response_id' => $autoResponseMessage->id
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to queue auto-response: ' . $e->getMessage());
        }
    }

    /**
     * Log unmatched messages for manual review.
     *
     * @param PhoneNumber $phoneNumber
     * @param TwilioMessage $message
     * @return void
     */
    public function logForManualReview(PhoneNumber $phoneNumber, TwilioMessage $message): void
    {
        // Update message status to indicate it needs manual review
        $message->update([
            'needs_manual_review' => true,
            'manual_review_at' => now()
        ]);

        Log::info('Message logged for manual review', [
            'phone_number_id' => $phoneNumber->id,
            'message_id' => $message->id,
            'message' => $message->response
        ]);
    }
}
