<?php

namespace App\Notifications;

use App\Models\Ride;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RideAccepted extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Ride $ride) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Votre course a été acceptée')
            ->greeting('Bonjour '.$notifiable->firstname.',')
            ->line($this->driverName().' a accepté votre course #'.$this->ride->reference().'.')
            ->line('Trajet : '.$this->ride->pickup_addr.' → '.$this->ride->dropoff_addr.'.')
            ->action('Voir la course', route('rides.show', $this->ride))
            ->salutation('À bientôt, l’équipe '.config('app.name'));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'ride_accepted',
            'icon' => '🚗',
            'title' => 'Course acceptée',
            'message' => $this->driverName().' a accepté votre course #'.$this->ride->reference().'.',
            'url' => route('rides.show', $this->ride),
        ];
    }

    private function driverName(): string
    {
        $driver = $this->ride->driver;

        return $driver ? trim($driver->firstname.' '.$driver->lastname) : 'Un chauffeur';
    }
}
