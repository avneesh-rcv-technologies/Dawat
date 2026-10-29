<?php

namespace Modules\Acl\Services;

use Illuminate\Validation\ValidationException;
use Modules\Acl\Models\Admin;
use Modules\Acl\Notifications\AdminPasswordResetNotification;
use Modules\EmailNotification\Models\NotificationTemplate;
use Modules\Notifications\Models\NotificationChannel;
use Modules\Notifications\Services\NotificationService;

class AdminPasswordResetService
{
    public function __construct(private NotificationService $notifications) {}

    public function send(Admin $admin, string $token): void
    {
        $mapping = (new AdminPasswordResetNotification())->getTemplateIdentifier();
        if (empty($mapping['email']) || empty($mapping['notify_account_owner'])
            || !NotificationChannel::where('name', 'email')->where('status', true)->exists()
            || !NotificationTemplate::whereKey($mapping['email'])->where('status', true)->exists()) {
            throw ValidationException::withMessages([
                'email' => ['Password reset email is not configured. Please contact the administrator.'],
            ]);
        }

        if (!$this->notifications->trigger('Acl', 'AdminPasswordReset', $admin, [
            'admin_name' => $admin->name,
            'reset_url' => route('admin.password.reset', ['token' => $token, 'email' => $admin->email]),
        ])) {
            throw ValidationException::withMessages([
                'email' => ['Unable to queue the password reset email. Please try again later.'],
            ]);
        }
    }
}
