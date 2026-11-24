<?php

namespace Modules\Acl\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Acl\Notifications\AdminEmailVerificationNotification;
use Modules\EmailNotification\Models\NotificationTemplate;
use Modules\EmailNotification\Support\SeederEmailView;
use Modules\Notifications\Models\NotificationChannel;
use Modules\Notifications\Models\NotificationEvent;
use Modules\Notifications\Models\NotificationMapping;

class AdminEmailVerificationSeeder extends Seeder
{
    public function run(): void
    {
        $event = NotificationEvent::firstOrCreate(['event_code' => AdminEmailVerificationNotification::EVENT], [
            'event_name' => 'Admin Email Verification',
            'event_description' => 'Verify the account email before signing in to the admin panel.',
            'notification_class' => AdminEmailVerificationNotification::class,
        ]);
        $template = NotificationTemplate::updateOrCreate(['name' => AdminEmailVerificationNotification::EVENT, 'locale' => 'en'], [
            'subject' => 'Verify your email — Dawat',
            'content' => SeederEmailView::make('acl::emails.admin-email-verification', [
                'accountName' => '{{admin_name}}',
                'verificationUrl' => '{{verification_url}}',
                'expiresMinutes' => '{{expires_minutes}}',
                'supportUrl' => '{{support_url}}',
                'currentYear' => '{{current_year}}',
            ])->render(),
            'status' => true,
        ]);
        $channel = NotificationChannel::where('name', 'email')->first();
        if ($channel) {
            NotificationMapping::firstOrCreate(['event_id' => $event->id, 'channel_id' => $channel->id], [
                'template_id' => (string) $template->id,
                'recipients' => ['notify_admin' => true],
            ]);
        }
    }
}
