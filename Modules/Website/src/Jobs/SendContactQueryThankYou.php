<?php

namespace Modules\Website\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;
use Modules\Website\Mail\ContactQueryThankYou;
use Modules\EmailNotification\Models\NotificationTemplate;
use Modules\Notifications\Models\NotificationLog;
use Modules\Website\Models\ContactQuery;

class SendContactQueryThankYou implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;
    public int $timeout = 30;

    public function __construct(public string $email, public string $firstName, public ?int $logId = null)
    {
    }

    public function backoff(): array
    {
        return [60, 300];
    }

    public function handle(): void
    {
        $log = $this->logId ? NotificationLog::find($this->logId) : null;
        // Also support jobs queued before notification logging was introduced.
        $log ??= NotificationLog::create([
            'event_code' => ContactQueryThankYou::TEMPLATE_NAME,
            'channel_name' => 'email',
            'notifiable_type' => (new ContactQuery)->getMorphClass(),
            'notifiable_id' => 0,
            'content' => ['recipient' => $this->email, 'template' => ContactQueryThankYou::TEMPLATE_NAME],
            'status' => 'queued',
        ]);
        $this->logId = $log->id;

        try {
            $log->update(['status' => 'processing', 'error_message' => null, 'sent_at' => null]);
            $this->sendThankYou($log);
        } catch (\Throwable $exception) {
            $log->update(['status' => 'failed', 'error_message' => $exception->getMessage(), 'sent_at' => null]);
            throw $exception;
        }
    }

    public function failed(?\Throwable $exception): void
    {
        if ($this->logId) {
            NotificationLog::whereKey($this->logId)->update([
                'status' => 'failed',
                'error_message' => $exception?->getMessage() ?? 'Email job failed.',
                'sent_at' => null,
            ]);
        }
    }

    protected function sendThankYou(NotificationLog $log): void
    {
        $template = NotificationTemplate::where('name', ContactQueryThankYou::TEMPLATE_NAME)
            ->where('locale', 'en')->where('status', true)->first();

        if (!$template) {
            $log->update(['status' => 'skipped', 'error_message' => 'Thank-you email template is missing or inactive.']);
            return;
        }

        $compiled = $template->compile([
            'first_name' => e($this->firstName),
            'email' => e($this->email),
            'app_name' => e(config('app.name', 'Dawat')),
            'website_url' => e(route('website.home')),
        ]);

        $log->update(['content' => [
            'recipient' => $this->email,
            'template' => ContactQueryThankYou::TEMPLATE_NAME,
            'subject' => $compiled['subject'],
            'body' => $compiled['content'],
        ]]);

        $driver = fn_get_setting('general.mail.driver') ?: config('mail.default');
        $mail = (new ContactQueryThankYou($this->firstName, $compiled))->from(
            fn_get_setting('general.mail.mail_from') ?: config('mail.from.address'),
            fn_get_setting('general.mail.mail_from_name') ?: config('mail.from.name', 'Dawat')
        );

        if ($driver === 'smtp') {
            $config = array_replace(config('mail.mailers.smtp', []), [
                'transport' => 'smtp',
                'host' => fn_get_setting('general.mail.host') ?: config('mail.mailers.smtp.host'),
                'port' => (int) (fn_get_setting('general.mail.port') ?: config('mail.mailers.smtp.port', 587)),
                'username' => fn_get_setting('general.mail.username') ?: config('mail.mailers.smtp.username'),
                'password' => fn_get_setting('general.mail.password') ?: config('mail.mailers.smtp.password'),
                'timeout' => 20,
            ]);

            $encryption = fn_get_setting('general.mail.encryption');
            if ($encryption !== null) {
                // Database SMTP settings take precedence over an environment MAIL_URL.
                unset($config['url']);
                $config['scheme'] = $encryption === 'ssl' ? 'smtps' : 'smtp';
                $config['auto_tls'] = $encryption !== 'none';
            }

            $mailer = Mail::build($config);
        } else {
            $mailer = Mail::mailer($driver);
        }

        $mailer->to($this->email)->send($mail);
        $log->update([
            'status' => $driver === 'log' ? 'logged' : 'sent',
            'sent_at' => $driver === 'log' ? null : now(),
            'error_message' => null,
        ]);
    }
}
