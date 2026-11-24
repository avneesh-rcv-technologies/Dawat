<?php

namespace Modules\Activitylog\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Modules\EmailNotification\Support\DawatEmail;

class OtpAccessRequestSeeder extends Seeder
{
    public function run(): void
    {
        $otp_templates = [
            [
                'name' => 'Admin Access OTP',
                'subject' => '{{company_name}} – Your OTP for admin access request.',
                'content' => DawatEmail::render([
                    'title' => 'Admin access code — Dawat',
                    'preheader' => 'Your OTP for the admin access request is {{otp}}.',
                    'badge' => 'ADMIN ACCESS',
                    'headline' => 'Your admin<br>access code',
                    'greeting' => 'Dear Admin,',
                    'intro' => 'Use the one-time code below to complete your admin access request.',
                    'blocks' => [
                        DawatEmail::code('One-time code', '{{otp}}', 'Valid for <strong style="color:#ff9a66;">5 minutes</strong>.'),
                        DawatEmail::callout('Keep your code private.', 'Do not share this code. If you did not request admin access, contact your administrator.'),
                    ],
                    'footer' => 'You received this email because admin access was requested for your account.',
                ]),
                'status' => 1,
                'locale' => 'en',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Error Notification',
                'subject' => ' {{severity}} error occurred in the system.',
                'content' => DawatEmail::render([
                    'title' => 'System error notification — Dawat',
                    'preheader' => 'A {{severity}} error occurred: {{message}}',
                    'badge' => 'SYSTEM ALERT',
                    'headline' => 'An error occurred<br>in the system',
                    'greeting' => 'Dear Developer,',
                    'intro' => 'An error occurred in the system. Details are below.',
                    'blocks' => [
                        DawatEmail::details('Error details', [
                            'Severity' => '<strong style="color:#ff9a66;">{{severity}}</strong>',
                            'Event' => '{{event}}',
                            'Message' => '{{message}}',
                            'User ID' => '{{user_id}}',
                            'Email' => '{{email}}',
                            'IP address' => '{{attempt_ip}}',
                            'User agent' => '{{user_agent}}',
                        ]),
                    ],
                    'signoff' => 'Thank you,',
                    'footer' => 'You received this email because you are on the Dawat error notification list.',
                ]),
                'status' => 1,
                'locale' => 'en',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]
        ];

        foreach ($otp_templates as $template) {
            DB::table('email_templates')->updateOrInsert(
                ['name' => $template['name'], 'locale' => 'en'], // Keep other locales intact
                $template // Values to insert or update
            );
        }


    }
}
