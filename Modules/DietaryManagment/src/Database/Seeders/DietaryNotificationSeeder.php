<?php

namespace Modules\DietaryManagment\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\DietaryManagment\Notifications\DietaryCertificateNotification;
use Modules\EmailNotification\Channels\EmailChannel;
use Modules\EmailNotification\Models\NotificationTemplate;
use Modules\EmailNotification\Support\SeederEmailView;
use Modules\Notifications\Models\NotificationChannel;
use Modules\Notifications\Models\NotificationEvent;
use Modules\Notifications\Models\NotificationMapping;
use Modules\WebPushNotification\Models\NotificationTemplate as PushTemplate;

class DietaryNotificationSeeder extends Seeder
{
    public function run(): void
    {
        $channel = NotificationChannel::firstOrCreate(['name' => 'email'], [
            'channel_class' => EmailChannel::class, 'status' => true, 'config' => [],
        ]);
        $pushChannel = NotificationChannel::where('name', 'webpush')->first();
        foreach (DietaryCertificateNotification::EVENTS as $action => $suffix) {
            $class = 'Modules\\DietaryManagment\\Notifications\\DietaryCertificate'.$suffix.'Notification';
            $notification = new $class();
            $label = ucfirst(str_replace('_', ' ', $action));
            $event = NotificationEvent::updateOrCreate(['event_code' => $notification->getEventCode()], [
                'event_name' => 'Dietary Certificate '.$label,
                'event_description' => 'Notifies the owner when a dietary certificate is '.$label.'. Submissions and resubmissions also notify admin.',
                'notification_class' => $class,
            ]);
            $template = NotificationTemplate::updateOrCreate(['name' => $notification->getEventCode(), 'locale' => 'en'], [
                'subject' => 'Dietary certificate '.strtolower($label).' — Dawat',
                'content' => SeederEmailView::make('dietarymanagment::emails.certificate-status', [
                    'action' => $action, 'statusLabel' => $label,
                    'restaurantName' => '{{restaurant_name}}', 'ownerName' => '{{owner_name}}',
                    'certificateName' => '{{certificate_name}}', 'dietaryLabel' => '{{dietary_label}}',
                    'fileName' => '{{file_name}}', 'submittedAt' => '{{submitted_at}}', 'reviewedAt' => '{{reviewed_at}}',
                    'adminNotes' => '{{admin_notes}}', 'actionUrl' => '{{action_url}}',
                    'supportUrl' => '{{support_url}}', 'currentYear' => '{{current_year}}',
                ])->render(),
                'status' => true,
            ]);
            NotificationMapping::firstOrCreate(['event_id' => $event->id, 'channel_id' => $channel->id], [
                'template_id' => (string) $template->id,
                'recipients' => ['notify_restaurant' => true, 'notify_admin' => in_array($action, ['submitted', 'resubmitted'], true)],
            ]);
            $push = PushTemplate::updateOrCreate(['name' => $notification->getEventCode()], [
                'subject' => 'Dietary certificate '.strtolower($label),
                'body' => match ($action) {
                    'submitted' => '{{certificate_name}} for {{restaurant_name}} was submitted and is awaiting review.',
                    'resubmitted' => '{{certificate_name}} for {{restaurant_name}} was resubmitted and is awaiting a new review.',
                    'under_review' => '{{certificate_name}} for {{restaurant_name}} is now under review.',
                    'approved' => '{{certificate_name}} for {{restaurant_name}} was approved.',
                    'rejected' => '{{certificate_name}} for {{restaurant_name}} was rejected. Feedback: {{admin_notes}}',
                    'changes_requested' => 'Changes requested for {{certificate_name}} at {{restaurant_name}}. Feedback: {{admin_notes}}',
                },
                'variables' => collect($notification->getAvailableVariables())->map(fn ($description, $name) => compact('name', 'description'))->values()->all(),
                'is_active' => true,
            ]);
            if ($pushChannel) {
                NotificationMapping::firstOrCreate(['event_id' => $event->id, 'channel_id' => $pushChannel->id], [
                    'template_id' => (string) $push->id,
                    'recipients' => ['notify_restaurant' => true, 'notify_admin' => in_array($action, ['submitted', 'resubmitted'], true)],
                ]);
            }
        }
    }
}
