<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContactMessage extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $senderName,
        public string $senderEmail,
        public string $subjectLabel,
        public string $body,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            replyTo: [new Address($this->senderEmail, $this->senderName)],
            subject: '[Contact KinTaxiBooking] '.$this->subjectLabel,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.contact',
        );
    }

    /**
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
