<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::table('email_templates')->where('name', 'contact_query_thank_you')->where('locale', 'en')->exists()) {
            return;
        }

        DB::table('email_templates')->insert([
            'name' => 'contact_query_thank_you',
            'subject' => 'Thank you for contacting Dawat',
            'content' => <<<'HTML'
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Thank you for contacting Dawat</title></head>
<body style="margin: 0; padding: 24px; background-color: #f6f6f6; font-family: Arial, sans-serif; color: #222;">
    {{header}}
    <div style="max-width: 600px; margin: 0 auto; padding: 32px; background-color: #fff; border-radius: 12px;">
        <h1 style="margin-top: 0; color: #ff6210; font-size: 28px;">Dawat</h1>
        <p>Hello {{first_name}},</p>
        <p>Thank you for contacting Dawat!</p>
        <p>We have received your message. Our team will review your enquiry and get back to you as soon as possible at the email address you provided.</p>
        <p>We appreciate you taking the time to reach out.</p>
        <p>Best regards,<br>The Dawat Team</p>
    </div>
    {{footer}}
</body>
</html>
HTML,
            'status' => true,
            'locale' => 'en',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        DB::table('email_templates')->where('name', 'contact_query_thank_you')->where('locale', 'en')->delete();
    }
};
