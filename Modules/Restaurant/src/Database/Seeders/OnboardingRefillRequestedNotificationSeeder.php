<?php

namespace Modules\Restaurant\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\EmailNotification\Support\SeederEmailView;
use Modules\EmailNotification\Models\NotificationTemplate as EmailTemplate;
use Modules\Notifications\Models\NotificationChannel;
use Modules\Notifications\Models\NotificationEvent;
use Modules\Notifications\Models\NotificationMapping;
use Modules\Restaurant\Notifications\OnboardingRefillRequestedNotification;
use Modules\WebPushNotification\Models\NotificationTemplate as WebPushTemplate;

class OnboardingRefillRequestedNotificationSeeder extends Seeder
{
    public function run(): void
    {
        $notification = new OnboardingRefillRequestedNotification();
        $code = $notification->getEventCode();
        $event = NotificationEvent::updateOrCreate(['event_code' => $code], [
            'event_name' => 'Onboarding Step Alert - Refill Requested',
            'event_description' => 'Notifies the owner when an administrator requests corrected onboarding details or a renewed KYC document, including after approval.',
            'notification_class' => OnboardingRefillRequestedNotification::class,
        ]);
        $email = EmailTemplate::updateOrCreate(['name' => $code, 'locale' => 'en'], [
            'subject' => 'Corrections requested: {{step_name}} — Dawat',
            'content' => $this->template(), 'status' => true, 'locale' => 'en',
        ]);
        $push = WebPushTemplate::updateOrCreate(['name' => $code], [
            'subject' => 'Onboarding Step Alert - Refill Requested',
            'body' => '{{step_name}}: refill requested for {{document_name}}. Reason: {{reason}}. Update and resubmit for review.',
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

    private function template(): string
    {
        return SeederEmailView::make('restaurant::emails.onboarding-refill-requested', [
            'ownerName' => '{{owner_name}}',
            'restaurantName' => '{{restaurant_name}}',
            'stepName' => '{{step_name}}',
            'documentName' => '{{document_name}}',
            'requestedAt' => '{{requested_at}}',
            'reason' => '{{reason}}',
            'correctionUrl' => '{{correction_url}}',
            'loginUrl' => '{{login_url}}',
            'supportUrl' => '{{support_url}}',
            'currentYear' => '{{current_year}}',
        ])->render();
    }
}
