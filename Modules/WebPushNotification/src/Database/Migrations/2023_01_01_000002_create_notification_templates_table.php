<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('webpush_notification_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('subject');
            $table->text('body');
            $table->json('variables')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Insert default templates
        $this->seedDefaultTemplates();
    }

    public function down()
    {
        Schema::dropIfExists('webpush_notification_templates');
    }

    protected function seedDefaultTemplates()
    {
        DB::table('webpush_notification_templates')->insert([
            [
                'name' => 'Default Notification',
                'subject' => 'New Notification',
                'body' => 'You have a new notification: {{ message }}',
                'variables' => json_encode(['message' => 'The notification message']),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Account Update',
                'subject' => 'Account Update',
                'body' => 'Your account has been updated. If this was not you, please contact support.',
                'variables' => json_encode([]),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
};
