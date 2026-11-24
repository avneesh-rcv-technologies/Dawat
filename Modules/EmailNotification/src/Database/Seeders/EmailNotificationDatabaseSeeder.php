<?php

namespace Modules\EmailNotification\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\EmailNotification\Models\NotificationTemplate;
use Modules\EmailNotification\Support\SeederEmailView;
use Modules\Website\Mail\ContactQueryThankYou;

class EmailNotificationDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        NotificationTemplate::firstOrCreate(
            ['name' => ContactQueryThankYou::TEMPLATE_NAME, 'locale' => 'en'],
            [
                'subject' => 'Thank you for contacting Dawat',
                'content' => $this->templateContent(),
                'status' => true,
            ]
        );
    }

    protected function templateContent(): string
    {
        return SeederEmailView::make('website::emails.contact-query-thank-you', [
            'firstName' => '{{first_name}}',
            'websiteUrl' => '{{website_url}}',
            'currentYear' => '{{current_year}}',
        ])->render();
    }
}
