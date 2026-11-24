<?php

namespace Modules\Restaurant\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\EmailNotification\Support\SeederEmailView;
use Modules\EmailNotification\Models\NotificationTemplate as EmailTemplate;
use Modules\Notifications\Models\NotificationChannel;
use Modules\Notifications\Models\NotificationEvent;
use Modules\Notifications\Models\NotificationMapping;
use Modules\Restaurant\Notifications\FinalOnboardingApprovedNotification;
use Modules\WebPushNotification\Models\NotificationTemplate as WebPushTemplate;

class FinalOnboardingApprovedNotificationSeeder extends Seeder
{
    public function run(): void
    {
        $code = 'final_onboarding_approved';
        $event = NotificationEvent::updateOrCreate(['event_code' => $code], [
            'notification_class' => FinalOnboardingApprovedNotification::class,
            'event_name' => 'Final Onboarding Approved',
            'event_description' => 'Notifies the restaurant owner when all required onboarding steps are approved, with an attached PDF summary.',
        ]);
        $email = EmailTemplate::updateOrCreate(['name' => $code, 'locale' => 'en'], [
            'subject' => 'Your restaurant onboarding is approved — Dawat',
            'content' => SeederEmailView::make('restaurant::emails.final-onboarding-approved', [
                'ownerName' => '{{owner_name}}',
                'restaurantName' => '{{restaurant_name}}',
                'approvedAt' => '{{approved_at}}',
                'summaryNote' => '{{summary_note}}',
                'loginUrl' => '{{login_url}}',
                'dashboardUrl' => '{{dashboard_url}}',
                'supportUrl' => '{{support_url}}',
                'currentYear' => '{{current_year}}',
            ])->render(),
            'status' => true, 'locale' => 'en',
        ]);
        $notification = new FinalOnboardingApprovedNotification();
        $push = WebPushTemplate::updateOrCreate(['name' => $code], [
            'subject' => 'Final Onboarding Approved',
            'body' => 'All required onboarding steps for {{restaurant_name}} were approved on {{approved_at}}. Your restaurant is now active. Your review PDF is attached to the email.',
            'variables' => collect($notification->getAvailableVariables())
                ->map(fn ($description, $name) => compact('name', 'description'))->values()->all(),
            'is_active' => true,
        ]);
        foreach (['email' => $email, 'webpush' => $push] as $name => $template) {
            $channel = NotificationChannel::where('name', $name)->first();
            if ($channel) {
                NotificationMapping::updateOrCreate(['event_id' => $event->id, 'channel_id' => $channel->id], [
                    'template_id' => (string) $template->id,
                    'recipients' => ['notify_restaurant' => true, 'notify_admin' => false],
                ]);
            }
        }
    }
}
