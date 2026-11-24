<?php

namespace Modules\Restaurant\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\EmailNotification\Support\SeederEmailView;
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
        $template = NotificationTemplate::updateOrCreate(['name' => RestaurantOnboardingOtpService::TYPE, 'locale' => 'en'], [
            'subject' => '{{otp_code}} — Verify your Dawat email',
            'content' => SeederEmailView::make('restaurant::emails.onboarding-email-verification', [
                'otpCode' => '{{otp_code}}',
                'expiresMinutes' => '{{expires_minutes}}',
                'recipientEmail' => '{{restaurant_email}}',
                'supportEmail' => '{{support_email}}',
                'currentYear' => '{{current_year}}',
            ])->render(),
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
