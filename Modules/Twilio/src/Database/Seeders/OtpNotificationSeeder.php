<?php

namespace Modules\Twilio\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\EmailNotification\Models\NotificationTemplate as EmailTemplate;
use Modules\Notifications\Models\NotificationEvent;
use Modules\Notifications\Models\NotificationChannel;
use Modules\Notifications\Models\NotificationMapping;
use Modules\Twilio\Models\SmsTemplate;

class OtpNotificationSeeder extends Seeder
{
    public function run(): void
    {
        $otpVerificationEvent = NotificationEvent::updateOrCreate(
            ['event_code' => 'otp_verification'],
            [
                'event_name' => 'OTP Verification',
                'event_description' => 'Triggered when a customer requests OTP for authentication',
            ]
        );

        $smsChannel = NotificationChannel::whereIn('name', ['sms', 'twilio'])->first();
        $emailChannel = NotificationChannel::where('name', 'email')->first();

        if (!$smsChannel && !$emailChannel) {
            $this->command->warn('No SMS or email channel found. Please ensure notification channels are properly configured.');
            return;
        }

        if ($smsChannel) {
            $smsTemplate = SmsTemplate::updateOrCreate(
                ['event_code' => 'otp_verification'],
                [
                    'name' => 'OTP Verification SMS',
                    'content' => 'Your verification code for {{action}} is: {{otp_code}}. Valid for {{expiry_minutes}} minutes. {{security_warning}}',
                    'is_active' => true,
                ]
            );

            NotificationMapping::updateOrCreate(
                [
                    'event_id' => $otpVerificationEvent->id,
                    'channel_id' => $smsChannel->id,
                ],
                [
                    'template_id' => $smsTemplate->id,
                    'recipients' => ['notify_user' => true],
                ]
            );
        }

        if ($emailChannel) {
            $emailTemplate = EmailTemplate::updateOrCreate(
                ['name' => 'OTP Verification Email'],
                [
                    'subject' => 'Your verification code for {{action}}',
                    'content' => $this->getEmailTemplate(),
                    'status' => true,
                ]
            );

            NotificationMapping::updateOrCreate(
                [
                    'event_id' => $otpVerificationEvent->id,
                    'channel_id' => $emailChannel->id,
                ],
                [
                    'template_id' => $emailTemplate->id,
                    'recipients' => ['notify_user' => true],
                ]
            );
        }

        $this->command->info('OTP Verification notification seeded successfully!');
    }

    protected function getEmailTemplate(): string
    {
        return <<<'HTML'
<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 24px; background: #f8fafc; color: #111827;">
    <div style="background: #ffffff; border: 1px solid #e5e7eb; border-radius: 12px; padding: 32px;">
        <h2 style="margin: 0 0 16px; font-size: 24px; color: #111827;">Verify your email</h2>
        <p style="margin: 0 0 16px; font-size: 16px; color: #374151; line-height: 1.6;">
            Hello,
        </p>
        <p style="margin: 0 0 20px; font-size: 16px; color: #374151; line-height: 1.6;">
            Your verification code for <strong>{{action}}</strong> is:
        </p>
        <div style="display: inline-block; padding: 18px 24px; background: #111827; color: #ffffff; font-size: 30px; letter-spacing: 6px; border-radius: 8px; font-weight: 700; margin: 0 0 20px;">
            {{otp_code}}
        </div>
        <p style="margin: 0 0 12px; font-size: 15px; color: #374151; line-height: 1.6;">
            This code is valid for {{expiry_minutes}} minutes.
        </p>
        <p style="margin: 0; font-size: 15px; color: #374151; line-height: 1.6;">
            {{security_warning}}
        </p>
    </div>
</div>
HTML;
    }
}

