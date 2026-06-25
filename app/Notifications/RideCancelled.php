<?php

namespace App\Notifications;

use App\Models\Ride;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class RideCancelled extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Ride $ride, public User $cancelledBy) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'ride_cancelled',
            'icon' => '🚫',
            'title' => 'Course annulée',
            'message' => 'La course #'.$this->ride->reference().' a été annulée par '.$this->actorLabel().'.',
            'url' => route('rides.show', $this->ride),
        ];
    }

    private function actorLabel(): string
    {
        return match ($this->cancelledBy->role) {
            'driver' => 'le chauffeur',
            'client' => 'le client',
            default => 'un administrateur',
        };
    }
}
