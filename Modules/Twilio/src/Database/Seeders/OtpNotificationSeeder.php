<?php

namespace Modules\Twilio\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Acl\Notifications\OtpVerificationNotification;
use Modules\EmailNotification\Models\NotificationTemplate as EmailTemplate;
use Modules\EmailNotification\Support\DawatEmail;
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
                'notification_class' => OtpVerificationNotification::class,
            ]
        );

        $smsChannel = NotificationChannel::whereIn('name', ['sms', 'twilio'])->first();
        $emailChannel = NotificationChannel::where('name', 'email')->first();

        if (! $smsChannel && ! $emailChannel) {
            $this->command?->warn('No SMS or email channel found. Please ensure notification channels are properly configured.');

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
                ['name' => 'OTP Verification Email', 'locale' => 'en'],
                [
                    'subject' => 'Your verification code for {{action}}',
                    'content' => $this->getEmailTemplate(),
                    'status' => true,
                    'locale' => 'en',
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

        $this->command?->info('OTP Verification notification seeded successfully!');
    }

    protected function getEmailTemplate(): string
    {
        return DawatEmail::render([
            'title' => 'Your verification code — Dawat',
            'preheader' => 'Your verification code for {{action}} is {{otp_code}}.',
            'badge' => 'VERIFICATION CODE',
            'headline' => 'One code.<br>You\'re almost in',
            'greeting' => 'Hello,',
            'intro' => 'Use the code below to complete <strong style="color:#ffffff;">{{action}}</strong>.',
            'blocks' => [
                DawatEmail::code('Your verification code', '{{otp_code}}', 'Valid for <strong style="color:#ff9a66;">{{expiry_minutes}} minutes</strong>.'),
                DawatEmail::callout('Keep your code private.', '{{security_warning}}'),
            ],
            'footer' => 'You received this email because a verification code was requested for your Dawat account.',
        ]);
    }
}
