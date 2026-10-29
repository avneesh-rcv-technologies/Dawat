<?php

namespace Modules\Twilio\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Twilio\Models\MessagingAutomation;
use Illuminate\Support\Facades\DB;

class MessagingAutomationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Only seed if no automation rules exist
        if (MessagingAutomation::count() > 0) {
            return;
        }

        $automationRules = [
            [
                'keyword' => 'STOP',
                'response_type' => 'text',
                'response_text' => 'You have been unsubscribed. You will no longer receive messages from us.',
                'is_active' => true,
                'priority' => 100,
                'case_sensitive' => false,
                'exact_match' => true,
                'admin_id' => 1, // Assuming admin ID 1 exists
            ],
            [
                'keyword' => 'HELP',
                'response_type' => 'text',
                'response_text' => 'For assistance, please contact our support team at support@example.com or call 1-800-123-4567.',
                'is_active' => true,
                'priority' => 90,
                'case_sensitive' => false,
                'exact_match' => true,
                'admin_id' => 1,
            ],
            [
                'keyword' => 'hours',
                'response_type' => 'text',
                'response_text' => 'Our business hours are Monday-Friday 9AM-6PM EST. We are closed on weekends and holidays.',
                'is_active' => true,
                'priority' => 80,
                'case_sensitive' => false,
                'exact_match' => false,
                'admin_id' => 1,
            ],
            [
                'keyword' => 'price',
                'response_type' => 'chatgpt',
                'response_text' => null,
                'chatgpt_prompt' => 'Provide pricing information for our services. Be helpful and informative.',
                'is_active' => true,
                'priority' => 70,
                'case_sensitive' => false,
                'exact_match' => false,
                'admin_id' => 1,
            ],
            [
                'keyword' => 'hello',
                'response_type' => 'text',
                'response_text' => 'Hello! Thank you for your message. How can we help you today?',
                'is_active' => true,
                'priority' => 60,
                'case_sensitive' => false,
                'exact_match' => false,
                'admin_id' => 1,
            ],
        ];

        foreach ($automationRules as $rule) {
            MessagingAutomation::create($rule);
        }
    }
}
