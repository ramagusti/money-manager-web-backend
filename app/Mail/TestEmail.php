<?php

namespace App\Mail;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use MailerSend\Helpers\Builder\Personalization;
use MailerSend\LaravelDriver\MailerSendTrait;

class TestEmail extends Mailable
{
    use Queueable, SerializesModels, MailerSendTrait;

    public function __construct()
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'MailerSend Test Email',
        );
    }

    public function content(): Content
    {
        $to = Arr::get($this->to, '0.address');

        // MailerSend-specific options
        $this->mailersend(
            template_id: null,
            tags: ['test-email'],
            personalization: [
                new Personalization($to, [
                    'var' => 'Hello!',
                    'number' => 123,
                ])
            ],
            precedenceBulkHeader: true,
            sendAt: new Carbon('2025-01-01 12:00:00'),
        );

        return new Content(
            view: 'emails.test_email',
            text: 'emails.test_email_text'
        );
    }
}
