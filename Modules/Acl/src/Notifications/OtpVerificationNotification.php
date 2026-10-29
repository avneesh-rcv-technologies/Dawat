<?php

namespace Modules\Acl\Notifications;

use Exception;
use Modules\Notifications\Contracts\Notification;
use Modules\Notifications\Models\NotificationEvent;
use Modules\Notifications\Models\NotificationChannel;
use Modules\Notifications\Models\NotificationMapping;

class OtpVerificationNotification implements Notification
{
    public function __construct(
        protected $entity = null,
        protected $data = []
    ) {
        // Extract data from entity if it's an array
        if (is_array($entity)) {
            $this->data = $entity;
            $this->entity = null;
        }
    }

    public function getEventCode(): string
    {
        return 'otp_verification';
    }

    public function getTemplateIdentifier(): array
    {
        $channels = [];

        try {
            $event = NotificationEvent::where('event_code', $this->getEventCode())->first();

            if (!$event) {
                return $channels;
            }

            $mappings = NotificationMapping::where('event_id', $event->id)->get();

            foreach ($mappings as $mapping) {
                $channel = NotificationChannel::find($mapping->channel_id);
                if ($channel) {
                    $channels[$channel->name] = $mapping->template_id;

                    // Add recipient flags from mapping
                    if (!empty($mapping->recipients)) {
                        $channels = array_merge($channels, $mapping->recipients);
                    }
                }
            }
        } catch (Exception $e) {
            // Log error if needed
        }

        return $channels;
    }

    public function getVariables(): array
    {
        // Extract data from the data array
        $phone = $this->data['phone'] ?? '';
        $otp = $this->data['otp'] ?? '';
        $purpose = $this->data['purpose'] ?? 'login';
        $expiryMinutes = $this->data['expiryMinutes'] ?? 5;
        
        $action = $purpose === 'register' ? 'registration' : 'login';
        $maskedPhone = $this->maskPhone($phone);

        return [
            'phone_number' => $phone,
            'masked_phone' => $maskedPhone,
            'otp_code' => $otp,
            'purpose' => $purpose,
            'action' => $action,
            'expiry_minutes' => $expiryMinutes,
            'expiry_seconds' => $expiryMinutes * 60,
            'verification_message' => $this->getVerificationMessage($phone, $otp, $purpose, $expiryMinutes),
            'security_warning' => 'Do not share this code with anyone.',
            'notification_type' => 'otp_verification',
        ];
    }

    public function getAvailableVariables(): array
    {
        return [
            'phone_number' => 'Customer phone number',
            'masked_phone' => 'Masked phone number (e.g., +123456****)',
            'otp_code' => 'One-time password code',
            'purpose' => 'Purpose of OTP (login, register, verify_phone)',
            'action' => 'Action description (login or registration)',
            'expiry_minutes' => 'OTP expiry time in minutes',
            'expiry_seconds' => 'OTP expiry time in seconds',
            'verification_message' => 'Complete verification message',
            'security_warning' => 'Security warning message',
        ];
    }

    public function getAvailableRecipients(): array
    {
        return [
            'notify_user' => 'Notify Customer',
        ];
    }

    public function getRecipientTypeMapping(): array
    {
        return [
            'notify_user' => 'notifiable',
        ];
    }

    public function getRecipientClassMapping(): array
    {
        return [
            'notify_user' => \Modules\Acl\Models\Customer::class,
        ];
    }

    public function toArray(): array
    {
        return [
            'event_code' => $this->getEventCode(),
            'template' => $this->getTemplateIdentifier(),
            'variables' => $this->getVariables(),
            'available_variables' => $this->getAvailableVariables(),
        ];
    }

    /**
     * Get verification message
     */
    private function getVerificationMessage(string $phone, string $otp, string $purpose, int $expiryMinutes): string
    {
        $action = $purpose === 'register' ? 'registration' : 'login';
        return "Your verification code for {$action} is: {$otp}. Valid for {$expiryMinutes} minutes. Do not share this code with anyone.";
    }

    /**
     * Mask phone number for display
     */
    private function maskPhone(?string $phone): string
    {
        if (!$phone) {
            return '';
        }

        $length = strlen($phone);
        if ($length <= 4) {
            return $phone;
        }

        return substr($phone, 0, -4) . str_repeat('*', 4);
    }
}
