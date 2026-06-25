<?php

namespace App\Mail;

use App\Models\Ride;
use App\Support\Money;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RideBookedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public Ride $ride) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Confirmation de réservation — '.$this->ride->reference(),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.ride-booked',
            with: [
                'priceLabel' => Money::fc($this->ride->price),
            ],
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
