<?php

namespace Modules\Restaurant\Database\Seeders;

use Illuminate\Database\Seeder;
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
                'event_name' => 'Restaurant Tax ' . $label,
                'event_description' => 'Notifies ' . ($isReview ? 'the owner' : 'the owner and admin') . ' when tax information is ' . $action . '.',
            ]);
            $template = EmailTemplate::updateOrCreate(['name' => $code], [
                'subject' => 'Tax Information ' . $label . ' - {{app_name}}',
                'content' => $this->template($label, $action), 'status' => true, 'locale' => 'en',
            ]);
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

    private function template(string $label, string $action): string
    {
        $summary = match ($action) {
            'approved' => 'The {{tax_type}} tax information for <strong>{{restaurant_name}}</strong> (application #{{restaurant_id}}) was approved on {{reviewed_at}}.',
            'rejected' => 'The {{tax_type}} tax information for <strong>{{restaurant_name}}</strong> (application #{{restaurant_id}}) was rejected on {{reviewed_at}}.',
            default => 'The {{tax_type}} tax information for <strong>{{restaurant_name}}</strong> (application #{{restaurant_id}}) was successfully saved on {{updated_at}}.',
        };
        $details = match ($action) {
            'approved' => 'Your tax step is approved. You can continue with the remaining onboarding steps.',
            'rejected' => '<strong>Rejection reason:</strong> {{rejection_reason}}<br>Please correct your tax details and resubmit them for review.',
            default => 'The tax step has been submitted for review. This confirmation does not mean the tax information or application has been approved.',
        };

        return str_replace(['%%ACTION%%', '%%SUMMARY%%', '%%DETAILS%%'], [$label, $summary, $details], <<<'HTML'
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0"><title>Tax Information %%ACTION%%</title></head>
<body style="margin:0;padding:0;background:#f4f7fa;font-family:Arial,sans-serif;">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0"><tr><td style="padding:40px 20px;">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:600px;margin:auto;background:#fff;border-radius:12px;">
<tr><td style="padding:32px;background:#0f766e;color:#fff;border-radius:12px 12px 0 0;"><h1 style="margin:0;font-size:24px;">Tax Information %%ACTION%%</h1></td></tr>
<tr><td style="padding:32px;color:#334155;font-size:15px;line-height:1.7;">
<p>%%SUMMARY%%</p>
<p>%%DETAILS%%</p>
<p>If you did not make this change, contact support immediately.</p>
</td></tr>
<tr><td style="padding:24px;background:#f8fafc;color:#64748b;text-align:center;border-radius:0 0 12px 12px;">{{app_name}} Team</td></tr>
</table></td></tr></table>
</body></html>
HTML);
    }
}
