<?php

namespace Modules\Restaurant\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\EmailNotification\Support\SeederEmailView;
use Modules\EmailNotification\Models\NotificationTemplate as EmailTemplate;
use Modules\Notifications\Models\NotificationChannel;
use Modules\Notifications\Models\NotificationEvent;
use Modules\Notifications\Models\NotificationMapping;
use Modules\Restaurant\Notifications\RestaurantTaxSubmittedNotification;
use Modules\Restaurant\Notifications\RestaurantTaxApprovedNotification;
use Modules\Restaurant\Notifications\RestaurantTaxRejectedNotification;
use Modules\WebPushNotification\Models\NotificationTemplate as WebPushTemplate;

class RestaurantTaxNotificationSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['submitted' => 'Submitted', 'approved' => 'Approved', 'rejected' => 'Rejected'] as $action => $label) {
            $code = 'restaurant_tax_' . $action;
            $isReview = in_array($action, ['approved', 'rejected'], true);
            $recipients = ['notify_restaurant' => true, 'notify_admin' => !$isReview];
            $notification = match ($action) {
                'approved' => new RestaurantTaxApprovedNotification(),
                'rejected' => new RestaurantTaxRejectedNotification(),
                default => new RestaurantTaxSubmittedNotification(),
            };
            $event = NotificationEvent::updateOrCreate(['event_code' => $code], [
                'notification_class' => get_class($notification),
                'event_name' => 'Restaurant Tax ' . $label,
                'event_description' => 'Notifies ' . ($isReview ? 'the owner' : 'the owner and admin') . ' when tax information is ' . $action . '.',
            ]);
            $template = $this->seedEmailTemplate($action);
            $pushTemplate = WebPushTemplate::updateOrCreate(['name' => $code], [
                'subject' => 'Tax Information ' . $label,
                'body' => match ($action) {
                    'approved' => '{{tax_type}} tax information for {{restaurant_name}} was approved on {{reviewed_at}}.',
                    'rejected' => '{{tax_type}} tax information for {{restaurant_name}} was rejected. Reason: {{rejection_reason}}. Please update and resubmit.',
                    default => '{{restaurant_name}} submitted {{tax_type}} tax information on {{updated_at}}. The tax step is awaiting review.',
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

    public function seedEmailTemplate(string $action): EmailTemplate
    {
        $copy = match ($action) {
            'submitted' => [
                'headline' => 'Received', 'status' => 'Submitted — awaiting review',
                'summary' => 'The tax step is now awaiting review by the Dawat team.',
                'nextSteps' => 'The review team will check the submitted information. The restaurant owner will receive an update when the tax step is approved or corrections are needed.',
            ],
            'resubmitted' => [
                'headline' => 'Resubmitted', 'status' => 'Resubmitted — awaiting review',
                'summary' => 'The updated tax details are awaiting a new review by the Dawat team.',
                'nextSteps' => 'The review team will check the resubmitted information. The restaurant owner will receive an update after the new review.',
            ],
            'approved' => [
                'headline' => 'Approved', 'status' => 'Approved',
                'summary' => 'Your tax step is complete. Any remaining onboarding steps retain their own review status.',
                'nextSteps' => 'Check the remaining steps in your restaurant account and complete any outstanding onboarding information.',
            ],
            'rejected' => [
                'headline' => 'Needs correction', 'status' => 'Rejected',
                'summary' => 'Review the reason below, correct your tax information, and resubmit it for review.',
                'nextSteps' => 'Correct the tax information and resubmit it for a new review.',
            ],
            default => throw new \InvalidArgumentException('Unsupported tax notification action.'),
        };
        $isReview = in_array($action, ['approved', 'rejected'], true);
        $isRejected = $action === 'rejected';
        $timeLabel = $isReview ? 'Reviewed on' : ($action === 'resubmitted' ? 'Resubmitted on' : 'Submitted on');

        return EmailTemplate::updateOrCreate(['name' => 'restaurant_tax_'.$action, 'locale' => 'en'], [
            'subject' => 'Tax information '.$action.($isRejected ? ' — Action required | Dawat' : ' — Dawat'),
            'content' => SeederEmailView::make('restaurant::emails.tax-notification', $copy + [
                'actionLabel' => $action,
                'badge' => 'TAX INFORMATION '.strtoupper($action),
                'detailsHeading' => $isReview ? 'REVIEW DETAILS' : ($action === 'resubmitted' ? 'RESUBMISSION DETAILS' : 'SUBMISSION DETAILS'),
                'timeLabel' => $timeLabel,
                'recordedAt' => $isReview ? '{{reviewed_at}}' : '{{submitted_at}}',
                'isRejected' => $isRejected,
                'rejectionReason' => '{{rejection_reason}}',
                'restaurantName' => '{{restaurant_name}}',
                'taxType' => '{{tax_type}}',
                'loginUrl' => '{{login_url}}',
                'supportUrl' => '{{support_url}}',
                'currentYear' => '{{current_year}}',
                'instructions' => $isRejected
                    ? 'Log in, open Tax Information, update the details, and submit them again for review.'
                    : 'Restaurant owners can log in to check the tax step and continue with the remaining onboarding information.',
                'buttonLabel' => $isRejected ? 'Update tax information' : 'View tax information',
            ])->render(),
            'status' => true,
        ]);
    }
}
