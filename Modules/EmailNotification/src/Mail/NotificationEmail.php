<?php

namespace Modules\EmailNotification\Mail;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Config;
use Modules\Notifications\Models\NotificationLog;

class NotificationEmail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Queue-related configurations
     */
    public int $tries = 3;
    public int $maxExceptions = 2;
    public int $timeout = 30;
    public bool $deleteWhenMissingModels = true;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public string $mail_subject,
        public string $content,
        public ?int $logId = null,
        public ?array $attachment = []
    ) {}

    /**
     * Build the message.
     */
    public function build()
    {
        $log = $this->logId
            ? NotificationLog::find($this->logId)
            : null;

        try {
            if ($log) {
                $log->update(['status' => 'processing']);
            }
            
            // Apply custom SMTP configuration if provided
            if (!empty(fn_get_setting('general.mail.driver')) && !empty(fn_get_setting('general.mail.host')) && !empty(fn_get_setting('general.mail.port')) && !empty(fn_get_setting('general.mail.username')) && !empty(fn_get_setting('general.mail.password')) && !empty(fn_get_setting('general.mail.mail_from'))) {
                $this->validateSmtpConfig();
                $this->configureCustomMailer();
            }

            // Start building the email
            $this->subject($this->mail_subject)
                ->html($this->content);

            // Add attachments if provided
            if (!empty($this->attachment)) {
                foreach ($this->attachment as $attachment) {
                    // $attachment can be a file path or an array with more details
                    if (is_string($attachment)) {
                        $this->attach($attachment);
                    } elseif (is_array($attachment)) {
                        if (isset($attachment['data'])) {
                            $data = ($attachment['encoding'] ?? null) === 'base64'
                                ? base64_decode($attachment['data'], true) : $attachment['data'];
                            if ($data === false) {
                                throw new \InvalidArgumentException('Invalid attachment data.');
                            }
                            $this->attachData($data, $attachment['name'], ['mime' => $attachment['mime'] ?? 'application/octet-stream']);
                            continue;
                        }
                        $this->attach(
                            $attachment['path'],
                            array_filter([
                                'as'   => $attachment['name'] ?? null,
                                'mime' => $attachment['mime'] ?? null,
                            ])
                        );
                    }
                }
            }

            if (!empty(fn_get_setting('general.mail.cc'))) {
                $cc = $this->addCcRecipients($this);
                if ($log) {
                    $log->update(['cc' => json_encode($cc)]);
                }
            }

            if (!empty(fn_get_setting('general.mail.bcc'))) {
                $bcc = $this->addBccRecipients($this);
                if ($log) {
                    $log->update(['bcc' => json_encode($bcc)]);
                }
            }

            if ($log) {
                $log->update([
                    'status'  => 'sent',
                    'sent_at' => now(),
                ]);
            }

            return $this;
        } catch (Exception $e) {
            if ($log) {
                $log->update([
                    'status'        => 'failed',
                    'error_message' => 'Email processing failed: ' . $e->getMessage(),
                ]);
            }
            throw $e;
        }
    }

    /**
     * Handle failed queue job.
     */
    public function failed(\Throwable $exception): void
    {
        if ($this->logId) {
            NotificationLog::where('id', $this->logId)->update([
                'status' => 'failed',
                'error_message' => 'Job failed: ' . $exception->getMessage(),
            ]);
        }
    }

    /**
     * Validate dynamic SMTP configuration.
     */
    protected function validateSmtpConfig(): void
    {
        $required = ['general.mail.driver', 'general.mail.host', 'general.mail.port', 'general.mail.username', 'general.mail.password', 'general.mail.mail_from'];

        foreach ($required as $key) {
            if (empty(fn_get_setting($key))) {
                throw new \InvalidArgumentException("Missing required SMTP configuration: {$key}");
            }
        }
    }

    /**
     * Apply custom mailer configuration dynamically.
     */
    protected function configureCustomMailer(): void
    {
        Config::set('mail.mailers.smtp', [
            'transport' => fn_get_setting('general.mail.driver') ?? 'smtp',
            'host'      => fn_get_setting('general.mail.host'),
            'port'      => fn_get_setting('general.mail.port'),
            'encryption' => fn_get_setting('general.mail.encryption') ?? 'tls',
            'username'  => fn_get_setting('general.mail.username'),
            'password'  => fn_get_setting('general.mail.password'),
            'timeout'   => fn_get_setting('general.mail.timeout') ?? null,
        ]);

        Config::set('mail.from', [
            'address' => fn_get_setting('general.mail.mail_from'),
            'name'    => fn_get_setting('general.mail.mail_from_name') ?? config('app.name'),
        ]);
    }

    protected function addCcRecipients(Mailable $email): array
    {
        $cc = $this->normalizeRecipients(fn_get_setting('general.mail.cc'));
        foreach ($cc as $recipient) {
            $email->cc($recipient);
        }
        return $cc;
    }

    protected function addBccRecipients(Mailable $email): array
    {
        $bcc = $this->normalizeRecipients(fn_get_setting('general.mail.bcc'));
        foreach ($bcc as $recipient) {
            $email->bcc($recipient);
        }
        return $bcc;
    }

    protected function normalizeRecipients($value): array
    {
        if (is_string($value)) {
            return array_filter(array_map('trim', explode(',', $value)));
        }
        if (is_array($value)) {
            return array_filter(array_map('trim', $value));
        }
        return [];
    }
}
