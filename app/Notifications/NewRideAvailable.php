<?php

namespace App\Notifications;

use App\Models\Ride;
use App\Support\Money;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewRideAvailable extends Notification implements ShouldQueue
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
            ->subject('Nouvelle course disponible')
            ->greeting('Bonjour '.$notifiable->firstname.',')
            ->line('Une nouvelle course est disponible : '.$this->ride->pickup_addr.' → '.$this->ride->dropoff_addr.'.')
            ->line('Montant estimé : '.Money::fc($this->ride->price).'.')
            ->action('Voir la course', route('rides.show', $this->ride))
            ->salutation('Bonne route, l’équipe '.config('app.name'));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'new_ride_available',
            'icon' => '🆕',
            'title' => 'Nouvelle course disponible',
            'message' => $this->ride->pickup_addr.' → '.$this->ride->dropoff_addr.' · '.Money::fc($this->ride->price),
            'url' => route('rides.show', $this->ride),
        ];
    }
}
