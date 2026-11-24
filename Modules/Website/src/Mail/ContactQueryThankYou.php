<?php

namespace Modules\Website\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class ContactQueryThankYou extends Mailable
{
    public const TEMPLATE_NAME = 'contact_query_thank_you';

    public function __construct(public string $firstName, public ?array $templateContent = null)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->templateContent['subject'] ?? 'Thank you for contacting Dawat');
    }

    public function content(): Content
    {
        return $this->templateContent !== null
            ? new Content(htmlString: $this->templateContent['content'])
            : new Content(view: 'website::emails.contact-query-thank-you');
    }
}
