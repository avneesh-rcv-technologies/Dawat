<?php

namespace Modules\Restaurant\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\EmailNotification\Models\NotificationTemplate;
use Modules\Notifications\Models\NotificationChannel;
use Modules\Notifications\Models\NotificationEvent;
use Modules\Notifications\Models\NotificationMapping;
use Modules\Restaurant\Notifications\OnboardingRestaurantEmailVerificationNotification;
use Modules\Restaurant\Services\RestaurantOnboardingOtpService;

class RestaurantOnboardingEmailVerificationSeeder extends Seeder
{
    public function run(): void
    {
        $event = NotificationEvent::firstOrCreate(['event_code' => RestaurantOnboardingOtpService::TYPE], [
            'event_name' => 'Restaurant Onboarding Email Verification',
            'event_description' => 'Verify the owner email before submitting a Partner with Us application.',
            'notification_class' => OnboardingRestaurantEmailVerificationNotification::class,
        ]);
        $template = NotificationTemplate::firstOrCreate(['name' => RestaurantOnboardingOtpService::TYPE], [
            'subject' => 'Verify Your Email for Partner with Us - {{otp_code}}',
            'content' => <<<'HTML'
<div style="font-family:Arial,sans-serif;max-width:600px;margin:auto;padding:32px;">
    <h1>Verify your email</h1>
    <p>Use this code to submit your Partner with Us application to {{app_name}}:</p>
    <p style="font-size:32px;font-weight:bold;letter-spacing:8px;">{{otp_code}}</p>
    <p>This code expires in {{expires_minutes}} minutes. Do not share it with anyone.</p>
    <p>If you did not request this code, you can ignore this email.</p>
</div>
HTML,
            'status' => true,
            'locale' => 'en',
        ]);
        $channel = NotificationChannel::where('name', 'email')->first();
        if ($channel) {
            NotificationMapping::firstOrCreate(['event_id' => $event->id, 'channel_id' => $channel->id], [
                'template_id' => (string) $template->id,
                'recipients' => ['notify_restaurant' => true],
            ]);
        }
    }
}
