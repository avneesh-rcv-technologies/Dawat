<?php

namespace Modules\Reports\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class ScheduledReportMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $subjectLine,
        public string $emailBody,
        public string $reportName,
        public array $meta = [],
        public ?string $attachmentPath = null,
        public ?string $attachmentName = null,
        public ?string $chartSvg = null
    ) {
    }

    public function build()
    {
        $mail = $this->subject($this->subjectLine)
            ->view('reports::emails.scheduled_report');

        if ($this->attachmentPath && Storage::exists($this->attachmentPath)) {
            $mail->attachData(
                Storage::get($this->attachmentPath),
                $this->attachmentName ?: basename($this->attachmentPath)
            );
        }

        if ($this->chartSvg) {
            $mail->attachData($this->chartSvg, 'report-chart.svg', [
                'mime' => 'image/svg+xml',
            ]);
        }

        return $mail;
    }
}
