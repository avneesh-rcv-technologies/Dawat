<?php

namespace Modules\Restaurant\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\EmailNotification\Support\DawatEmail;
use Modules\EmailNotification\Support\SeederEmailView;
use Modules\EmailNotification\Models\NotificationTemplate as EmailTemplate;
use Modules\Notifications\Models\NotificationChannel;
use Modules\Notifications\Models\NotificationEvent;
use Modules\Notifications\Models\NotificationMapping;
use Modules\Restaurant\Notifications\RestaurantBankingSubmittedNotification;
use Modules\Restaurant\Notifications\RestaurantBankingApprovedNotification;
use Modules\WebPushNotification\Models\NotificationTemplate as WebPushTemplate;

class RestaurantBankingNotificationSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['submitted' => 'Submitted', 'updated' => 'Updated', 'approved' => 'Approved', 'rejected' => 'Rejected'] as $action => $label) {
            $code = 'restaurant_banking_' . $action;
            $isReview = in_array($action, ['approved', 'rejected'], true);
            $recipients = ['notify_restaurant' => true, 'notify_admin' => !$isReview];
            $notificationClass = 'Modules\\Restaurant\\Notifications\\RestaurantBanking'.ucfirst($action).'Notification';
            $notification = new $notificationClass();
            $event = NotificationEvent::updateOrCreate(['event_code' => $code], [
                'notification_class' => $notificationClass,
                'event_name' => 'Restaurant Banking ' . $label,
                'event_description' => 'Notifies ' . ($isReview ? 'the owner' : 'the owner and admin') . ' when banking and company details are ' . $action . '.',
            ]);
            $template = match ($action) {
                'submitted' => $this->seedSubmittedEmailTemplate(),
                'approved', 'rejected' => $this->seedReviewEmailTemplate($action),
                default => EmailTemplate::updateOrCreate(['name' => $code, 'locale' => 'en'], [
                    'subject' => 'Banking Information ' . $label . ' - {{app_name}}',
                    'content' => $this->template($label, $action), 'status' => true, 'locale' => 'en',
                ]),
            };
            $pushTemplate = WebPushTemplate::updateOrCreate(['name' => $code], [
                'subject' => 'Banking Information ' . $label,
                'body' => match ($action) {
                    'approved' => 'Banking details for {{restaurant_name}} were approved on {{reviewed_at}}.',
                    'rejected' => 'Banking details for {{restaurant_name}} were rejected. Reason: {{rejection_reason}}. Please update and resubmit.',
                    'submitted' => '{{restaurant_name}} submitted banking and company details on {{updated_at}}. The banking step is awaiting review.',
                    default => '{{restaurant_name}} updated banking and company details on {{updated_at}}. The banking step has been resubmitted for review.',
                },
                'variables' => collect($notification->getAvailableVariables())
                    ->map(fn ($description, $name) => ['name' => $name, 'description' => $description])
                    ->values()->all(),
                'is_active' => true,
            ]);
            $channel = NotificationChannel::where('name', 'email')->first();
            if ($channel) {
                NotificationMapping::updateOrCreate(['event_id' => $event->id, 'channel_id' => $channel->id], [
                    'template_id' => (string) $template->id,
                    'recipients' => $recipients,
                ]);
            }
            $pushChannel = NotificationChannel::where('name', 'webpush')->first();
            if ($pushChannel) {
                NotificationMapping::updateOrCreate(['event_id' => $event->id, 'channel_id' => $pushChannel->id], [
                    'template_id' => (string) $pushTemplate->id,
                    'recipients' => $recipients,
                ]);
            }
        }
    }

    public function seedSubmittedEmailTemplate(): EmailTemplate
    {
        return EmailTemplate::updateOrCreate(['name' => 'restaurant_banking_submitted', 'locale' => 'en'], [
            'subject' => 'Banking information received — Dawat',
            'content' => SeederEmailView::make('restaurant::emails.banking-submitted', [
                'restaurantName' => '{{restaurant_name}}',
                'submittedAt' => '{{submitted_at}}',
                'loginUrl' => '{{login_url}}',
                'supportUrl' => '{{support_url}}',
                'currentYear' => '{{current_year}}',
            ])->render(),
            'status' => true,
        ]);
    }

    public function seedReviewEmailTemplate(string $action): EmailTemplate
    {
        $approved = match ($action) {
            'approved' => true,
            'rejected' => false,
            default => throw new \InvalidArgumentException('Expected an approved or rejected banking review.'),
        };

        return EmailTemplate::updateOrCreate(['name' => 'restaurant_banking_'.$action, 'locale' => 'en'], [
            'subject' => $approved ? 'Banking information approved — Dawat' : 'Banking information rejected — Action required | Dawat',
            'content' => SeederEmailView::make('restaurant::emails.banking-reviewed', [
                'approved' => $approved,
                'restaurantName' => '{{restaurant_name}}',
                'reviewedAt' => '{{reviewed_at}}',
                'rejectionReason' => '{{rejection_reason}}',
                'loginUrl' => '{{login_url}}',
                'supportUrl' => '{{support_url}}',
                'currentYear' => '{{current_year}}',
            ])->render(),
            'status' => true,
        ]);
    }

    private function template(string $label, string $action): string
    {
        $summary = match ($action) {
            'approved' => 'The banking and company information for <strong style="color:#ffffff;">{{restaurant_name}}</strong> (application #{{restaurant_id}}) was approved on {{reviewed_at}}.',
            'rejected' => 'The banking and company information for <strong style="color:#ffffff;">{{restaurant_name}}</strong> (application #{{restaurant_id}}) was rejected on {{reviewed_at}}.',
            default => 'The banking and company information for <strong style="color:#ffffff;">{{restaurant_name}}</strong> (application #{{restaurant_id}}) was saved on {{updated_at}}.',
        };
        $details = match ($action) {
            'approved' => DawatEmail::callout('Banking step approved', 'You can continue with the remaining onboarding steps.'),
            'rejected' => DawatEmail::callout('Rejection reason', '{{rejection_reason}}'."\n".'Please correct your banking or company details and resubmit them for review.'),
            default => DawatEmail::callout('Awaiting review', 'The banking step has been resubmitted for review. This confirmation does not mean the bank account or application has been approved.'),
        };

        return DawatEmail::render([
            'title' => 'Banking information '.strtolower($label).' — Dawat',
            'preheader' => 'Banking information for {{restaurant_name}} was '.$action.'.',
            'badge' => 'BANKING '.strtoupper($label),
            'headline' => 'Banking information<br>'.strtolower($label),
            'greeting' => 'Hello,',
            'intro' => $summary,
            'blocks' => [$details],
            'notes' => 'If you did not make this change, contact support immediately.',
            'footer' => 'You received this email because banking details changed for a restaurant on Dawat.',
        ]);
    }
}
