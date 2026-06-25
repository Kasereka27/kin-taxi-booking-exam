<?php

namespace App\Mail;

use App\Models\Ride;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RideStatusMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Ride $ride,
        public string $event,
        public ?User $actor = null,
    ) {}

    public function envelope(): Envelope
    {
        $subject = match ($this->event) {
            'accepted' => 'Votre course a été acceptée — '.$this->ride->reference(),
            'cancelled' => 'Course annulée — '.$this->ride->reference(),
            default => 'Mise à jour de votre course — '.$this->ride->reference(),
        };

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.ride-status',
            with: [
                'headline' => $this->headline(),
                'details' => $this->details(),
            ],
        );
    }

    private function headline(): string
    {
        return match ($this->event) {
            'accepted' => 'Un chauffeur a accepté votre course.',
            'cancelled' => 'La course a été annulée.',
            default => 'Le statut de votre course a changé.',
        };
    }

    private function details(): string
    {
        if ($this->event === 'accepted') {
            $driver = $this->ride->driver;

            return $driver
                ? trim($driver->firstname.' '.$driver->lastname).' prendra en charge votre trajet.'
                : 'Un chauffeur prendra en charge votre trajet.';
        }

        if ($this->event === 'cancelled' && $this->actor) {
            return trim($this->actor->firstname.' '.$this->actor->lastname).' a annulé la course.';
        }

        return 'Consultez les détails de la course dans votre espace.';
    }

    /**
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
