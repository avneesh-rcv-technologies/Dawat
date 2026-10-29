<?php

namespace Modules\Acl\Services;

use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\ValidationException;
use Modules\Acl\Models\Admin;
use Modules\Acl\Notifications\AdminEmailVerificationNotification;
use Modules\Notifications\Models\NotificationChannel;
use Modules\Notifications\Services\NotificationService;

class AdminEmailVerificationService
{
    public function __construct(private NotificationService $notifications) {}

    public function send(Admin $admin): void
    {
        $key = 'admin-email-verification:'.$admin->id.':'.sha1($admin->email);
        $sent = RateLimiter::attempt($key, 1, function () use ($admin) {
            $mapping = (new AdminEmailVerificationNotification())->getTemplateIdentifier();
            if (empty($mapping['email']) || empty($mapping['notify_admin'])
                || !NotificationChannel::where('name', 'email')->where('status', true)->exists()) {
                throw ValidationException::withMessages([
                    'email' => ['Email verification is not available. Please contact the administrator.'],
                ]);
            }

            $url = URL::temporarySignedRoute('admin.verification.verify', now()->addMinutes(60), [
                'id' => $admin->id,
                'hash' => sha1($admin->email),
            ]);
            if (!$this->notifications->trigger('Acl', 'AdminEmailVerification', $admin, [
                'admin_name' => $admin->name,
                'verification_url' => $url,
                'expires_minutes' => 60,
            ])) {
                throw ValidationException::withMessages([
                    'email' => ['Unable to send the verification email. Please try again later.'],
                ]);
            }

            return true;
        }, 60);

        if (!$sent) {
            throw ValidationException::withMessages([
                'email' => ['A verification link was recently requested. Please wait '.RateLimiter::availableIn($key).' seconds before trying again.'],
            ]);
        }
    }
}
